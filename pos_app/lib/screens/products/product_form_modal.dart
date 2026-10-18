import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/network/api_client.dart';
import '../../models/category_model.dart';
import '../../models/product_model.dart';

class ProductFormModal extends StatefulWidget {
  final ProductModel? existingProduct;
  final List<CategoryModel> categories;
  final VoidCallback onSaved;

  const ProductFormModal({
    super.key,
    this.existingProduct,
    required this.categories,
    required this.onSaved,
  });

  @override
  State<ProductFormModal> createState() => _ProductFormModalState();
}

class _ProductFormModalState extends State<ProductFormModal> {
  final _formKey = GlobalKey<FormState>();

  late TextEditingController _nameController;
  late TextEditingController _codeController;
  late TextEditingController _sellingPriceController;
  late TextEditingController _purchasePriceController;
  late TextEditingController _stockController;
  late TextEditingController _imageUrlController;
  late TextEditingController _descriptionController;

  int? _selectedCategoryId;
  bool _isGstApplicable = false;
  double _gstRate = 5.0; // Default GST rate if applicable
  bool _isSubmitting = false;

  final List<double> _standardGstRates = [5.0, 12.0, 18.0, 28.0];

  @override
  void initState() {
    super.initState();
    final p = widget.existingProduct;

    _nameController = TextEditingController(text: p?.name ?? '');
    _codeController = TextEditingController(text: p?.code ?? '');
    _sellingPriceController = TextEditingController(
      text: p != null ? p.retailerPrice.toStringAsFixed(2) : '',
    );
    _purchasePriceController = TextEditingController(
      text: p != null && p.purchasePrice > 0 ? p.purchasePrice.toStringAsFixed(2) : '',
    );
    _stockController = TextEditingController(
      text: p != null ? p.stock.toInt().toString() : '100',
    );
    _imageUrlController = TextEditingController(text: p?.imageUrl ?? '');
    _descriptionController = TextEditingController(text: p?.description ?? '');

    _selectedCategoryId = p?.categoryId ?? (widget.categories.isNotEmpty ? widget.categories.first.id : null);
    _isGstApplicable = (p?.taxRate ?? 0) > 0;
    _gstRate = _isGstApplicable ? p!.taxRate : 5.0;
  }

  @override
  void dispose() {
    _nameController.dispose();
    _codeController.dispose();
    _sellingPriceController.dispose();
    _purchasePriceController.dispose();
    _stockController.dispose();
    _imageUrlController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);

    try {
      final payload = {
        'name': _nameController.text.trim(),
        'category_id': _selectedCategoryId,
        if (_codeController.text.trim().isNotEmpty) 'code': _codeController.text.trim(),
        'retailer_price': double.tryParse(_sellingPriceController.text.trim()) ?? 0.0,
        'purchase_price': double.tryParse(_purchasePriceController.text.trim()) ?? 0.0,
        'dealer_price': double.tryParse(_sellingPriceController.text.trim()) ?? 0.0,
        'wholesale_price': double.tryParse(_sellingPriceController.text.trim()) ?? 0.0,
        'stock': double.tryParse(_stockController.text.trim()) ?? 100.0,
        'tax_rate': _isGstApplicable ? _gstRate : 0.0, // GST yes = rate, otherwise 0
        'image_url': _imageUrlController.text.trim().isNotEmpty ? _imageUrlController.text.trim() : null,
        'description': _descriptionController.text.trim().isNotEmpty ? _descriptionController.text.trim() : null,
      };

      if (widget.existingProduct == null) {
        // Create product
        final res = await ApiClient().dio.post('/pos/products', data: payload);
        if (res.data['success'] == true && mounted) {
          Navigator.pop(context);
          widget.onSaved();
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Product added successfully!'),
              backgroundColor: AppColors.success,
            ),
          );
        }
      } else {
        // Update product
        final res = await ApiClient().dio.put(
          '/pos/products/${widget.existingProduct!.id}',
          data: payload,
        );
        if (res.data['success'] == true && mounted) {
          Navigator.pop(context);
          widget.onSaved();
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Product updated successfully!'),
              backgroundColor: AppColors.success,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error saving product: $e'),
            backgroundColor: AppColors.danger,
          ),
        );
      }
    }

    setState(() => _isSubmitting = false);
  }

  Future<void> _deleteProduct() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete Product'),
        content: Text('Are you sure you want to delete "${widget.existingProduct!.name}"?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger, foregroundColor: Colors.white),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Delete'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    setState(() => _isSubmitting = true);
    try {
      final res = await ApiClient().dio.delete('/pos/products/${widget.existingProduct!.id}');
      if (res.data['success'] == true && mounted) {
        Navigator.pop(context);
        widget.onSaved();
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Product deleted successfully!'), backgroundColor: AppColors.danger),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Delete failed: $e')));
      }
    }
    setState(() => _isSubmitting = false);
  }

  @override
  Widget build(BuildContext context) {
    final isEditing = widget.existingProduct != null;

    return Container(
      height: MediaQuery.of(context).size.height * 0.9,
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        children: [
          // Drag Handle
          Center(
            child: Container(
              margin: const EdgeInsets.only(top: 12),
              width: 48,
              height: 4,
              decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2)),
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  isEditing ? 'Edit Product Item' : 'Create New Product',
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                ),
                IconButton(
                  icon: const Icon(Icons.close),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
          ),
          const Divider(height: 1),

          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Item Name
                    const Text('Item Name *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: _nameController,
                      decoration: InputDecoration(
                        hintText: 'e.g. Classic Hot Coffee, Veg Grilled Sandwich',
                        prefixIcon: const Icon(Icons.inventory_2_outlined, color: AppColors.primary),
                        filled: true,
                        fillColor: AppColors.background,
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                      ),
                      validator: (val) => (val == null || val.trim().isEmpty) ? 'Please enter item name' : null,
                    ),
                    const SizedBox(height: 16),

                    // Category Selector
                    const Text('Category', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<int>(
                      initialValue: _selectedCategoryId,
                      decoration: InputDecoration(
                        prefixIcon: const Icon(Icons.category_outlined, color: AppColors.primary),
                        filled: true,
                        fillColor: AppColors.background,
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                      ),
                      items: widget.categories.map((c) {
                        return DropdownMenuItem<int>(
                          value: c.id,
                          child: Text(c.name),
                        );
                      }).toList(),
                      onChanged: (val) => setState(() => _selectedCategoryId = val),
                    ),
                    const SizedBox(height: 16),

                    // Amount / Selling Price & Cost Price
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Selling Price (₹) *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                              const SizedBox(height: 6),
                              TextFormField(
                                controller: _sellingPriceController,
                                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                decoration: InputDecoration(
                                  hintText: '40.00',
                                  prefixText: '₹ ',
                                  prefixStyle: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary),
                                  filled: true,
                                  fillColor: AppColors.background,
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                                ),
                                validator: (val) {
                                  if (val == null || val.trim().isEmpty) return 'Price required';
                                  if (double.tryParse(val) == null) return 'Invalid number';
                                  return null;
                                },
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Purchase Cost (₹)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                              const SizedBox(height: 6),
                              TextFormField(
                                controller: _purchasePriceController,
                                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                decoration: InputDecoration(
                                  hintText: '15.00',
                                  prefixText: '₹ ',
                                  prefixStyle: const TextStyle(color: AppColors.textSecondary),
                                  filled: true,
                                  fillColor: AppColors.background,
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),

                    // Stock & SKU / Barcode
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Initial Stock Qty', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                              const SizedBox(height: 6),
                              TextFormField(
                                controller: _stockController,
                                keyboardType: TextInputType.number,
                                decoration: InputDecoration(
                                  hintText: '100',
                                  prefixIcon: const Icon(Icons.production_quantity_limits_rounded, color: AppColors.primary, size: 20),
                                  filled: true,
                                  fillColor: AppColors.background,
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Item Code / SKU', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                              const SizedBox(height: 6),
                              TextFormField(
                                controller: _codeController,
                                decoration: InputDecoration(
                                  hintText: 'e.g. COF-001',
                                  prefixIcon: const Icon(Icons.qr_code_rounded, color: AppColors.primary, size: 20),
                                  filled: true,
                                  fillColor: AppColors.background,
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),

                    // GST Applicable Section (Clean Yes / No Toggle)
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: _isGstApplicable ? Colors.blue.shade50.withValues(alpha: 0.5) : AppColors.background,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: _isGstApplicable ? AppColors.primary.withValues(alpha: 0.3) : AppColors.border),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Row(
                                children: [
                                  Icon(
                                    Icons.receipt_rounded,
                                    color: _isGstApplicable ? AppColors.primary : AppColors.textSecondary,
                                    size: 20,
                                  ),
                                  const SizedBox(width: 8),
                                  const Text(
                                    'GST Applicable?',
                                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                  ),
                                ],
                              ),
                              Switch(
                                value: _isGstApplicable,
                                activeTrackColor: AppColors.primary,
                                activeThumbColor: Colors.white,
                                onChanged: (val) => setState(() => _isGstApplicable = val),
                              ),
                            ],
                          ),
                          Text(
                            _isGstApplicable
                                ? 'Yes: GST tax will be calculated on sale & printed on thermal receipt.'
                                : 'No: GST is not applicable for this item (0% tax exempt).',
                            style: TextStyle(
                              fontSize: 12,
                              color: _isGstApplicable ? AppColors.primary : AppColors.textSecondary,
                            ),
                          ),
                          if (_isGstApplicable) ...[
                            const SizedBox(height: 12),
                            const Text('Select GST Rate %:', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 12)),
                            const SizedBox(height: 8),
                            Wrap(
                              spacing: 8,
                              children: _standardGstRates.map((rate) {
                                final isSel = _gstRate == rate;
                                return ChoiceChip(
                                  label: Text('${rate.toInt()}% GST'),
                                  selected: isSel,
                                  selectedColor: AppColors.primary,
                                  labelStyle: TextStyle(
                                    color: isSel ? Colors.white : AppColors.textPrimary,
                                    fontWeight: isSel ? FontWeight.bold : FontWeight.normal,
                                    fontSize: 12,
                                  ),
                                  onSelected: (_) => setState(() => _gstRate = rate),
                                );
                              }).toList(),
                            ),
                          ],
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Product Image (Optional - Not Compulsory)
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: AppColors.background,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Row(
                            children: [
                              Icon(Icons.image_outlined, color: AppColors.primary, size: 20),
                              SizedBox(width: 8),
                              Text(
                                'Product Image',
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                              ),
                              SizedBox(width: 6),
                              Text(
                                '(Optional - Not Compulsory)',
                                style: TextStyle(color: Colors.green, fontWeight: FontWeight.bold, fontSize: 11),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          const Text(
                            'You do not need an image. If left blank, a clean cafe icon will be displayed automatically.',
                            style: TextStyle(color: AppColors.textSecondary, fontSize: 11),
                          ),
                          const SizedBox(height: 10),
                          TextFormField(
                            controller: _imageUrlController,
                            decoration: InputDecoration(
                              hintText: 'https://example.com/item.jpg (Optional)',
                              prefixIcon: const Icon(Icons.link_rounded, size: 18),
                              filled: true,
                              fillColor: Colors.white,
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.border)),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Description / Notes (Optional)
                    const Text('Item Description / Recipe Notes (Optional)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: _descriptionController,
                      maxLines: 2,
                      decoration: InputDecoration(
                        hintText: 'e.g. Prepared with fresh milk, chocolate sauce, and steamed froth...',
                        filled: true,
                        fillColor: AppColors.background,
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                      ),
                    ),
                    const SizedBox(height: 28),

                    // Action Buttons
                    ElevatedButton(
                      onPressed: _isSubmitting ? null : _submit,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                        minimumSize: const Size.fromHeight(52),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        elevation: 2,
                      ),
                      child: _isSubmitting
                          ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                          : Text(
                              isEditing ? 'Save Product Changes' : 'Create Product Item',
                              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                            ),
                    ),

                    if (isEditing) ...[
                      const SizedBox(height: 12),
                      OutlinedButton.icon(
                        onPressed: _isSubmitting ? null : _deleteProduct,
                        icon: const Icon(Icons.delete_outline, color: AppColors.danger, size: 20),
                        label: const Text('Delete This Product', style: TextStyle(color: AppColors.danger)),
                        style: OutlinedButton.styleFrom(
                          minimumSize: const Size.fromHeight(48),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          side: const BorderSide(color: AppColors.danger),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
