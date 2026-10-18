import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/network/api_client.dart';
import '../../models/category_model.dart';
import '../../models/product_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import '../../providers/printer_provider.dart';
import '../printer/printer_settings_screen.dart';
import 'cart_sheet.dart';

class PosScreen extends StatefulWidget {
  const PosScreen({super.key});

  @override
  State<PosScreen> createState() => _PosScreenState();
}

class _PosScreenState extends State<PosScreen> {
  List<ProductModel> _products = [];
  List<CategoryModel> _categories = [];
  int? _selectedCategoryId;
  bool _isLoading = false;
  final _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _fetchCategories();
    _fetchProducts();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchCategories() async {
    try {
      final res = await ApiClient().dio.get('/pos/categories');
      if (res.data['success'] == true) {
        final list = (res.data['data'] as List)
            .map((c) => CategoryModel.fromJson(c))
            .toList();
        setState(() => _categories = list);
      }
    } catch (_) {}
  }

  Future<void> _fetchProducts({String? search, int? categoryId}) async {
    setState(() => _isLoading = true);
    final cart = context.read<CartProvider>();

    try {
      final query = <String, dynamic>{
        'tier': cart.activeTier,
      };
      if (search != null && search.isNotEmpty) query['search'] = search;
      if (categoryId != null) query['category_id'] = categoryId;

      final res = await ApiClient().dio.get('/pos/products', queryParameters: query);

      if (res.data['success'] == true) {
        final list = (res.data['data']['data'] as List)
            .map((p) => ProductModel.fromJson(p))
            .toList();
        setState(() => _products = list);
      }
    } catch (e) {
      debugPrint('Error fetching products: $e');
    }

    setState(() => _isLoading = false);
  }

  void _openBarcodeScanner() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.black,
      builder: (ctx) {
        return SizedBox(
          height: MediaQuery.of(ctx).size.height * 0.7,
          child: Column(
            children: [
              AppBar(
                title: const Text('Scan Product Barcode', style: TextStyle(color: Colors.white)),
                backgroundColor: Colors.transparent,
                leading: IconButton(
                  icon: const Icon(Icons.close, color: Colors.white),
                  onPressed: () => Navigator.pop(ctx),
                ),
              ),
              Expanded(
                child: MobileScanner(
                  onDetect: (capture) async {
                    final List<Barcode> barcodes = capture.barcodes;
                    if (barcodes.isNotEmpty && barcodes.first.rawValue != null) {
                      final code = barcodes.first.rawValue!;
                      Navigator.pop(ctx);
                      _lookupBarcode(code);
                    }
                  },
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _lookupBarcode(String code) async {
    final cart = context.read<CartProvider>();
    try {
      final res = await ApiClient().dio.get('/pos/products/barcode/$code', queryParameters: {
        'tier': cart.activeTier,
      });

      if (res.data['success'] == true) {
        final product = ProductModel.fromJson(res.data['product']);
        cart.addToCart(product);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Added ${product.name} to cart'),
              backgroundColor: AppColors.success,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Product with barcode $code not found')),
        );
      }
    }
  }

  void _showCartSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => const CartSheet(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final cart = context.watch<CartProvider>();
    final printer = context.watch<PrinterProvider>();
    final sym = auth.currencySymbol;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Text(
                  auth.shop?['name'] ?? 'NesCafe Solapur',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    auth.roleDisplayName,
                    style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.white),
                  ),
                ),
              ],
            ),
            Text(
              'Cashier: ${auth.user?['name'] ?? 'Staff'}',
              style: const TextStyle(fontSize: 11, color: Colors.white70),
            ),
          ],
        ),
        actions: [
          // Bluetooth Printer Status indicator button
          TextButton.icon(
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const PrinterSettingsScreen()),
              );
            },
            icon: Icon(
              printer.isConnected ? Icons.print_rounded : Icons.print_disabled_rounded,
              color: printer.isConnected ? Colors.greenAccent : Colors.amberAccent,
              size: 20,
            ),
            label: Text(
              printer.isConnected ? printer.paperSizeLabel : 'No Printer',
              style: TextStyle(
                color: printer.isConnected ? Colors.greenAccent : Colors.amberAccent,
                fontSize: 11,
              ),
            ),
          ),
          IconButton(
            icon: const Icon(Icons.qr_code_scanner_rounded),
            tooltip: 'Barcode Scanner',
            onPressed: _openBarcodeScanner,
          ),
        ],
      ),
      body: Column(
        children: [
          // Search Bar
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 6),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search coffees, snacks, items...',
                prefixIcon: const Icon(Icons.search, color: AppColors.primary),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () {
                          _searchController.clear();
                          _fetchProducts(categoryId: _selectedCategoryId);
                        },
                      )
                    : null,
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(color: AppColors.border),
                ),
              ),
              onSubmitted: (val) => _fetchProducts(search: val, categoryId: _selectedCategoryId),
            ),
          ),

          // Horizontal Category Tabs Bar
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
            child: Row(
              children: [
                ChoiceChip(
                  label: const Text('All Items'),
                  selected: _selectedCategoryId == null,
                  selectedColor: AppColors.primary,
                  labelStyle: TextStyle(
                    color: _selectedCategoryId == null ? Colors.white : AppColors.textPrimary,
                    fontWeight: _selectedCategoryId == null ? FontWeight.bold : FontWeight.normal,
                    fontSize: 12,
                  ),
                  onSelected: (_) {
                    setState(() => _selectedCategoryId = null);
                    _fetchProducts(search: _searchController.text.trim());
                  },
                ),
                const SizedBox(width: 8),
                ..._categories.map((c) {
                  final isSel = _selectedCategoryId == c.id;
                  return Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: ChoiceChip(
                      label: Text(c.name),
                      selected: isSel,
                      selectedColor: AppColors.primary,
                      labelStyle: TextStyle(
                        color: isSel ? Colors.white : AppColors.textPrimary,
                        fontWeight: isSel ? FontWeight.bold : FontWeight.normal,
                        fontSize: 12,
                      ),
                      onSelected: (_) {
                        setState(() => _selectedCategoryId = c.id);
                        _fetchProducts(
                          search: _searchController.text.trim(),
                          categoryId: c.id,
                        );
                      },
                    ),
                  );
                }),
              ],
            ),
          ),

          // Products Grid
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _products.isEmpty
                    ? const Center(child: Text('No products available in this category.'))
                    : GridView.builder(
                        padding: const EdgeInsets.fromLTRB(16, 6, 16, 80),
                        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: 2,
                          childAspectRatio: 0.78,
                          crossAxisSpacing: 12,
                          mainAxisSpacing: 12,
                        ),
                        itemCount: _products.length,
                        itemBuilder: (context, index) {
                          final product = _products[index];
                          product.updateTier(cart.activeTier);

                          // Check if item is already in cart
                          final inCartItem = cart.items.cast<dynamic>().firstWhere(
                                (i) => i.product.id == product.id,
                                orElse: () => null,
                              );
                          final qtyInCart = inCartItem != null ? inCartItem.quantity.toInt() : 0;
                          final hasGst = product.taxRate > 0;

                          return Card(
                            elevation: 2,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                            color: Colors.white,
                            child: Padding(
                              padding: const EdgeInsets.all(10),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  // Product Image Thumbnail or Fallback Icon
                                  Expanded(
                                    child: ClipRRect(
                                      borderRadius: BorderRadius.circular(10),
                                      child: Container(
                                        width: double.infinity,
                                        color: AppColors.background,
                                        child: (product.imageUrl != null && product.imageUrl!.startsWith('http'))
                                            ? Image.network(
                                                product.imageUrl!,
                                                fit: BoxFit.cover,
                                                errorBuilder: (context, error, stackTrace) => const Icon(
                                                  Icons.coffee_rounded,
                                                  size: 38,
                                                  color: AppColors.primary,
                                                ),
                                              )
                                            : const Icon(
                                                Icons.coffee_rounded,
                                                size: 38,
                                                color: AppColors.primary,
                                              ),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(height: 8),

                                  // Name
                                  Text(
                                    product.name,
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.textPrimary),
                                  ),
                                  const SizedBox(height: 3),

                                  // GST Badge & SKU
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                                        decoration: BoxDecoration(
                                          color: hasGst ? Colors.amber.shade50 : Colors.green.shade50,
                                          borderRadius: BorderRadius.circular(4),
                                        ),
                                        child: Text(
                                          hasGst ? 'GST ${product.taxRate.toInt()}%' : 'No GST',
                                          style: TextStyle(
                                            fontSize: 9,
                                            fontWeight: FontWeight.bold,
                                            color: hasGst ? Colors.amber.shade900 : Colors.green.shade800,
                                          ),
                                        ),
                                      ),
                                      Text(
                                        'Stock: ${product.stock.toInt()}',
                                        style: TextStyle(
                                          fontSize: 10,
                                          color: product.stock <= product.alertQuantity ? Colors.red : Colors.grey.shade600,
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 6),

                                  // Price & Add to Cart / Stepper
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        '$sym${product.activePrice.toStringAsFixed(2)}',
                                        style: const TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: 15,
                                          color: AppColors.primary,
                                        ),
                                      ),
                                      if (qtyInCart == 0)
                                        InkWell(
                                          onTap: () {
                                            cart.addToCart(product);
                                            ScaffoldMessenger.of(context).hideCurrentSnackBar();
                                            ScaffoldMessenger.of(context).showSnackBar(
                                              SnackBar(
                                                content: Text('Added ${product.name}'),
                                                duration: const Duration(milliseconds: 600),
                                              ),
                                            );
                                          },
                                          child: Container(
                                            padding: const EdgeInsets.all(6),
                                            decoration: const BoxDecoration(
                                              color: AppColors.primary,
                                              shape: BoxShape.circle,
                                            ),
                                            child: const Icon(Icons.add, size: 16, color: Colors.white),
                                          ),
                                        )
                                      else
                                        Row(
                                          children: [
                                            InkWell(
                                              onTap: () => cart.updateQuantity(product.id, (qtyInCart - 1).toDouble()),
                                              child: Container(
                                                padding: const EdgeInsets.all(4),
                                                decoration: BoxDecoration(
                                                  color: Colors.grey.shade200,
                                                  shape: BoxShape.circle,
                                                ),
                                                child: const Icon(Icons.remove, size: 14, color: AppColors.textPrimary),
                                              ),
                                            ),
                                            Padding(
                                              padding: const EdgeInsets.symmetric(horizontal: 6),
                                              child: Text(
                                                '$qtyInCart',
                                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                              ),
                                            ),
                                            InkWell(
                                              onTap: () => cart.updateQuantity(product.id, (qtyInCart + 1).toDouble()),
                                              child: Container(
                                                padding: const EdgeInsets.all(4),
                                                decoration: const BoxDecoration(
                                                  color: AppColors.primary,
                                                  shape: BoxShape.circle,
                                                ),
                                                child: const Icon(Icons.add, size: 14, color: Colors.white),
                                              ),
                                            ),
                                          ],
                                        ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
          ),

          // Bottom Floating Cart Bar
          if (cart.items.isNotEmpty)
            GestureDetector(
              onTap: _showCartSheet,
              child: Container(
                margin: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                decoration: BoxDecoration(
                  color: AppColors.primary,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.primary.withValues(alpha: 0.35),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.shopping_cart_checkout_rounded, color: Colors.white),
                        const SizedBox(width: 12),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              '${cart.itemCount} Items in Cart',
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                            Text(
                              'Tap to Checkout & Print',
                              style: const TextStyle(color: Colors.white70, fontSize: 11),
                            ),
                          ],
                        ),
                      ],
                    ),
                    Row(
                      children: [
                        Text(
                          '$sym${cart.grandTotal.toStringAsFixed(2)}',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18),
                        ),
                        const SizedBox(width: 8),
                        const Icon(Icons.arrow_forward_ios_rounded, color: Colors.white, size: 16),
                      ],
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}
