import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/network/api_client.dart';
import '../../models/category_model.dart';
import '../../models/product_model.dart';
import '../../providers/auth_provider.dart';
import 'product_form_modal.dart';

class ProductsScreen extends StatefulWidget {
  const ProductsScreen({super.key});

  @override
  State<ProductsScreen> createState() => _ProductsScreenState();
}

class _ProductsScreenState extends State<ProductsScreen> {
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
    try {
      final query = <String, dynamic>{};
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
      debugPrint('Error loading products: $e');
    }
    setState(() => _isLoading = false);
  }

  void _openProductForm([ProductModel? product]) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => ProductFormModal(
        existingProduct: product,
        categories: _categories,
        onSaved: () => _fetchProducts(
          search: _searchController.text.trim(),
          categoryId: _selectedCategoryId,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final canManage = auth.isManagerOrAbove;
    final sym = auth.currencySymbol;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Menu & Products', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        actions: [
          if (canManage)
            IconButton(
              icon: const Icon(Icons.add_circle_outline_rounded),
              tooltip: 'Add New Product',
              onPressed: () => _openProductForm(),
            ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Reload',
            onPressed: () {
              _fetchCategories();
              _fetchProducts(
                search: _searchController.text.trim(),
                categoryId: _selectedCategoryId,
              );
            },
          ),
        ],
      ),
      floatingActionButton: canManage
          ? FloatingActionButton.extended(
              backgroundColor: AppColors.primary,
              foregroundColor: Colors.white,
              icon: const Icon(Icons.add_rounded),
              label: const Text('Add Product', style: TextStyle(fontWeight: FontWeight.bold)),
              onPressed: () => _openProductForm(),
            )
          : null,
      body: Column(
        children: [
          // Search Bar
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search products by name or code...',
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
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 0),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide.none),
              ),
              onSubmitted: (val) => _fetchProducts(search: val, categoryId: _selectedCategoryId),
            ),
          ),

          // Categories Horizontal Filter Bar
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(
              children: [
                ChoiceChip(
                  label: const Text('All Categories'),
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

          // Products Count / Permission Header
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 4),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  '${_products.length} Products Available',
                  style: const TextStyle(color: AppColors.textSecondary, fontSize: 12, fontWeight: FontWeight.w600),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: canManage ? AppColors.primary.withValues(alpha: 0.1) : Colors.grey.shade200,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    canManage ? 'Manager Access (Tap to Edit)' : 'Cashier (View Only)',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: canManage ? AppColors.primary : Colors.grey.shade700,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 4),

          // Product List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _products.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.inventory_2_outlined, size: 54, color: Colors.grey.shade400),
                            const SizedBox(height: 12),
                            const Text('No products found.', style: TextStyle(color: AppColors.textSecondary)),
                            if (canManage) ...[
                              const SizedBox(height: 12),
                              ElevatedButton.icon(
                                onPressed: () => _openProductForm(),
                                icon: const Icon(Icons.add, size: 18),
                                label: const Text('Add First Product'),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppColors.primary,
                                  foregroundColor: Colors.white,
                                ),
                              ),
                            ],
                          ],
                        ),
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.fromLTRB(16, 8, 16, 80),
                        itemCount: _products.length,
                        itemBuilder: (context, index) {
                          final p = _products[index];
                          final hasGst = p.taxRate > 0;

                          return Card(
                            elevation: 1.5,
                            margin: const EdgeInsets.only(bottom: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                            color: Colors.white,
                            child: InkWell(
                              borderRadius: BorderRadius.circular(16),
                              onTap: canManage ? () => _openProductForm(p) : null,
                              child: Padding(
                                padding: const EdgeInsets.all(12),
                                child: Row(
                                  children: [
                                    // Product Image (if available) or Category Icon Fallback
                                    ClipRRect(
                                      borderRadius: BorderRadius.circular(12),
                                      child: Container(
                                        width: 60,
                                        height: 60,
                                        color: AppColors.background,
                                        child: (p.imageUrl != null && p.imageUrl!.startsWith('http'))
                                            ? Image.network(
                                                p.imageUrl!,
                                                fit: BoxFit.cover,
                                                errorBuilder: (context, error, stackTrace) => const Icon(
                                                  Icons.coffee_rounded,
                                                  color: AppColors.primary,
                                                  size: 32,
                                                ),
                                              )
                                            : const Icon(
                                                Icons.coffee_rounded,
                                                color: AppColors.primary,
                                                size: 32,
                                              ),
                                      ),
                                    ),
                                    const SizedBox(width: 14),

                                    // Product Info
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            p.name,
                                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: AppColors.textPrimary),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                          const SizedBox(height: 3),
                                          Row(
                                            children: [
                                              if (p.categoryName != null)
                                                Container(
                                                  margin: const EdgeInsets.only(right: 6),
                                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                  decoration: BoxDecoration(
                                                    color: AppColors.primary.withValues(alpha: 0.08),
                                                    borderRadius: BorderRadius.circular(4),
                                                  ),
                                                  child: Text(
                                                    p.categoryName!,
                                                    style: const TextStyle(fontSize: 10, color: AppColors.primary, fontWeight: FontWeight.w600),
                                                  ),
                                                ),
                                              Text(
                                                'SKU: ${p.code}',
                                                style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                                              ),
                                            ],
                                          ),
                                          const SizedBox(height: 6),
                                          Row(
                                            children: [
                                              // Price
                                              Text(
                                                '$sym${p.retailerPrice.toStringAsFixed(2)}',
                                                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.primary),
                                              ),
                                              const SizedBox(width: 10),
                                              // GST Badge
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                decoration: BoxDecoration(
                                                  color: hasGst ? Colors.amber.shade50 : Colors.green.shade50,
                                                  borderRadius: BorderRadius.circular(6),
                                                  border: Border.all(
                                                    color: hasGst ? Colors.amber.shade300 : Colors.green.shade300,
                                                    width: 0.8,
                                                  ),
                                                ),
                                                child: Text(
                                                  hasGst ? 'GST ${p.taxRate.toInt()}%' : 'No GST (0%)',
                                                  style: TextStyle(
                                                    fontSize: 10,
                                                    fontWeight: FontWeight.bold,
                                                    color: hasGst ? Colors.amber.shade900 : Colors.green.shade800,
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ),
                                        ],
                                      ),
                                    ),

                                    // Stock badge & Edit arrow
                                    Column(
                                      crossAxisAlignment: CrossAxisAlignment.end,
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                          decoration: BoxDecoration(
                                            color: p.stock > 10 ? Colors.green.shade50 : Colors.red.shade50,
                                            borderRadius: BorderRadius.circular(6),
                                          ),
                                          child: Text(
                                            'Stock: ${p.stock.toInt()}',
                                            style: TextStyle(
                                              fontSize: 11,
                                              fontWeight: FontWeight.bold,
                                              color: p.stock > 10 ? Colors.green.shade700 : Colors.red.shade700,
                                            ),
                                          ),
                                        ),
                                        if (canManage) ...[
                                          const SizedBox(height: 8),
                                          const Icon(Icons.edit_outlined, size: 18, color: AppColors.textSecondary),
                                        ],
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }
}
