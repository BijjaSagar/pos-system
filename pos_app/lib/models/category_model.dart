class CategoryModel {
  final int id;
  final String name;
  final String? code;
  final String? icon;

  CategoryModel({
    required this.id,
    required this.name,
    this.code,
    this.icon,
  });

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    return CategoryModel(
      id: json['id'],
      name: json['name'] ?? '',
      code: json['code'],
      icon: json['icon'],
    );
  }
}
