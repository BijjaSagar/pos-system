import 'product_model.dart';

class CartItemModel {
  final ProductModel product;
  double quantity;
  double unitPrice;

  CartItemModel({
    required this.product,
    this.quantity = 1.0,
    required this.unitPrice,
  });

  double get subtotal => quantity * unitPrice;
}
