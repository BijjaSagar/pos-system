import 'package:flutter/material.dart';
import '../core/network/api_client.dart';
import '../models/cart_item_model.dart';
import '../models/customer_model.dart';
import '../models/product_model.dart';

class CartProvider extends ChangeNotifier {
  final List<CartItemModel> _items = [];
  CustomerModel? _selectedCustomer;
  String _activeTier = 'retailer'; // retailer, dealer, wholesaler
  double _discountAmount = 0.0;
  double _taxRate = 0.0;
  bool _isCheckingOut = false;

  List<CartItemModel> get items => _items;
  CustomerModel? get selectedCustomer => _selectedCustomer;
  String get activeTier => _activeTier;
  double get discountAmount => _discountAmount;
  bool get isCheckingOut => _isCheckingOut;
  int get itemCount => _items.fold(0, (sum, i) => sum + i.quantity.toInt());

  double get subtotal => _items.fold(0.0, (sum, i) => sum + i.subtotal);
  double get taxAmount => (subtotal - _discountAmount) * (_taxRate / 100.0);
  double get grandTotal => (subtotal - _discountAmount + taxAmount).clamp(0.0, double.infinity);

  void setCustomer(CustomerModel? customer) {
    _selectedCustomer = customer;
    if (customer != null) {
      setTier(customer.tier);
    } else {
      setTier('retailer');
    }
  }

  void setTier(String tier) {
    _activeTier = tier;
    // Recalibrate unit prices of all existing cart items based on the new tier!
    for (var item in _items) {
      item.product.updateTier(tier);
      item.unitPrice = item.product.activePrice;
    }
    notifyListeners();
  }

  void setDiscount(double discount) {
    _discountAmount = discount;
    notifyListeners();
  }

  void setTaxRate(double rate) {
    _taxRate = rate;
    notifyListeners();
  }

  void addToCart(ProductModel product, {double qty = 1.0}) {
    product.updateTier(_activeTier);
    final index = _items.indexWhere((i) => i.product.id == product.id);

    if (index >= 0) {
      _items[index].quantity += qty;
    } else {
      _items.add(CartItemModel(
        product: product,
        quantity: qty,
        unitPrice: product.activePrice,
      ));
    }
    notifyListeners();
  }

  void updateQuantity(int productId, double quantity) {
    final index = _items.indexWhere((i) => i.product.id == productId);
    if (index >= 0) {
      if (quantity <= 0) {
        _items.removeAt(index);
      } else {
        _items[index].quantity = quantity;
      }
      notifyListeners();
    }
  }

  void removeFromCart(int productId) {
    _items.removeWhere((i) => i.product.id == productId);
    notifyListeners();
  }

  void clearCart() {
    _items.clear();
    _discountAmount = 0.0;
    _selectedCustomer = null;
    _activeTier = 'retailer';
    notifyListeners();
  }

  /// Execute checkout against the Laravel POS API
  Future<Map<String, dynamic>?> checkout({
    required double paidAmount,
    required String paymentMethod,
    String? note,
  }) async {
    if (_items.isEmpty) return null;

    _isCheckingOut = true;
    notifyListeners();

    try {
      final payload = {
        'party_id': _selectedCustomer?.id,
        'discount_amount': _discountAmount,
        'tax_amount': taxAmount,
        'paid_amount': paidAmount,
        'payment_method': paymentMethod,
        'note': note,
        'items': _items.map((i) => {
          'product_id': i.product.id,
          'quantity': i.quantity,
          'unit_price': i.unitPrice,
        }).toList(),
      };

      final response = await ApiClient().dio.post('/pos/checkout', data: payload);

      if (response.data['success'] == true) {
        clearCart();
        _isCheckingOut = false;
        notifyListeners();
        return response.data['thermal_receipt_payload'];
      }
    } catch (e) {
      debugPrint('Checkout error: $e');
    }

    _isCheckingOut = false;
    notifyListeners();
    return null;
  }
}
