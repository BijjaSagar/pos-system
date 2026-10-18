import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/network/api_client.dart';
import '../../models/customer_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import 'receipt_screen.dart';

class CartSheet extends StatefulWidget {
  const CartSheet({super.key});

  @override
  State<CartSheet> createState() => _CartSheetState();
}

class _CartSheetState extends State<CartSheet> {
  List<CustomerModel> _customers = [];
  bool _loadingCustomers = false;
  String _paymentMethod = 'cash';
  final _paidController = TextEditingController();
  final _discountController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _fetchCustomers();
  }

  @override
  void dispose() {
    _paidController.dispose();
    _discountController.dispose();
    super.dispose();
  }

  Future<void> _fetchCustomers() async {
    setState(() => _loadingCustomers = true);
    try {
      final res = await ApiClient().dio.get('/pos/customers');
      if (res.data['success'] == true) {
        final list = (res.data['customers'] as List)
            .map((c) => CustomerModel.fromJson(c))
            .toList();
        setState(() => _customers = list);
      }
    } catch (_) {}
    setState(() => _loadingCustomers = false);
  }

  void _onCompleteSale() async {
    final cart = context.read<CartProvider>();
    final paid = double.tryParse(_paidController.text) ?? cart.grandTotal;

    final receiptData = await cart.checkout(
      paidAmount: paid,
      paymentMethod: _paymentMethod,
    );

    if (receiptData != null && mounted) {
      Navigator.of(context).pop(); // Close cart sheet
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => ReceiptScreen(thermalPayload: receiptData),
        ),
      );
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Failed to complete sale. Check stock levels.')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();
    final auth = context.watch<AuthProvider>();
    final sym = auth.currencySymbol;

    if (_paidController.text.isEmpty && cart.grandTotal > 0) {
      _paidController.text = cart.grandTotal.toStringAsFixed(2);
    }

    return Container(
      height: MediaQuery.of(context).size.height * 0.85,
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        children: [
          // Drag Handle & Header
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
                Text('Current Cart (${cart.itemCount} items)', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                TextButton(
                  onPressed: cart.items.isEmpty ? null : () => cart.clearCart(),
                  child: const Text('Clear All', style: TextStyle(color: Colors.red)),
                ),
              ],
            ),
          ),
          const Divider(height: 1),

          // Customer Selector & Tier Bar
          Container(
            color: AppColors.background,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            child: Column(
              children: [
                Row(
                  children: [
                    const Icon(Icons.person_pin_rounded, color: AppColors.primary, size: 22),
                    const SizedBox(width: 8),
                    Expanded(
                      child: _loadingCustomers
                          ? const Text('Loading customers...', style: TextStyle(fontSize: 13))
                          : DropdownButtonHideUnderline(
                              child: DropdownButton<int>(
                                hint: const Text('Select Customer / Walk-In', style: TextStyle(fontSize: 14)),
                                value: cart.selectedCustomer?.id,
                                isExpanded: true,
                                items: [
                                  const DropdownMenuItem<int>(
                                    value: null,
                                    child: Text('Walk-In Customer (Standard)'),
                                  ),
                                  ..._customers.map((c) => DropdownMenuItem<int>(
                                    value: c.id,
                                    child: Text('${c.name} (${c.tier.toUpperCase()})'),
                                  )),
                                ],
                                onChanged: (val) {
                                  if (val == null) {
                                    cart.setCustomer(null);
                                  } else {
                                    final found = _customers.firstWhere((c) => c.id == val);
                                    cart.setCustomer(found);
                                  }
                                },
                              ),
                            ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                // Customer Tier Selector (Recalibrates prices)
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      const Text('Price Tier: ', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
                      const SizedBox(width: 6),
                      _tierChip(cart, 'Retailer', 'retailer'),
                      const SizedBox(width: 6),
                      _tierChip(cart, 'Dealer', 'dealer'),
                      const SizedBox(width: 6),
                      _tierChip(cart, 'Wholesale', 'wholesaler'),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Items List
          Expanded(
            child: cart.items.isEmpty
                ? const Center(
                    child: Text('Cart is empty. Add products from the POS terminal.'),
                  )
                : ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: cart.items.length,
                    separatorBuilder: (_, index) => const Divider(height: 16),
                    itemBuilder: (context, index) {
                      final item = cart.items[index];
                      return Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  item.product.name,
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                Text(
                                  'Unit: $sym${item.unitPrice.toStringAsFixed(2)} | Subtotal: $sym${item.subtotal.toStringAsFixed(2)}',
                                  style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                                ),
                              ],
                            ),
                          ),
                          Row(
                            children: [
                              IconButton(
                                icon: const Icon(Icons.remove_circle_outline, color: AppColors.primary, size: 22),
                                onPressed: () => cart.updateQuantity(item.product.id, item.quantity - 1),
                              ),
                              Text('${item.quantity.toInt()}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                              IconButton(
                                icon: const Icon(Icons.add_circle_outline, color: AppColors.primary, size: 22),
                                onPressed: () => cart.updateQuantity(item.product.id, item.quantity + 1),
                              ),
                            ],
                          ),
                        ],
                      );
                    },
                  ),
          ),

          // Footer Checkout Summary
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(color: Colors.black.withValues(alpha: 0.06), blurRadius: 10, offset: const Offset(0, -4)),
              ],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Payment Method', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.textSecondary)),
                const SizedBox(height: 8),
                SizedBox(
                  width: double.infinity,
                  child: SegmentedButton<String>(
                    segments: const [
                      ButtonSegment(value: 'cash', label: Text('Cash'), icon: Icon(Icons.payments_outlined, size: 16)),
                      ButtonSegment(value: 'card', label: Text('Card / UPI'), icon: Icon(Icons.credit_card_outlined, size: 16)),
                      ButtonSegment(value: 'due', label: Text('Due Balance'), icon: Icon(Icons.pending_actions_outlined, size: 16)),
                    ],
                    selected: {_paymentMethod},
                    onSelectionChanged: (val) => setState(() => _paymentMethod = val.first),
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Total Amount:', style: TextStyle(fontSize: 16)),
                    Text('$sym${cart.grandTotal.toStringAsFixed(2)}', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: AppColors.primary)),
                  ],
                ),
                const SizedBox(height: 16),
                ElevatedButton(
                  onPressed: (cart.items.isEmpty || cart.isCheckingOut) ? null : _onCompleteSale,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    foregroundColor: Colors.white,
                    minimumSize: const Size.fromHeight(50),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  child: cart.isCheckingOut
                      ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : const Text('Complete Sale & Print Receipt', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _tierChip(CartProvider cart, String label, String value) {
    final isSelected = cart.activeTier == value;
    return ChoiceChip(
      label: Text(label, style: TextStyle(fontSize: 11, color: isSelected ? Colors.white : Colors.black87)),
      selected: isSelected,
      selectedColor: AppColors.primary,
      backgroundColor: Colors.white,
      padding: EdgeInsets.zero,
      onSelected: (_) => cart.setTier(value),
    );
  }
}
