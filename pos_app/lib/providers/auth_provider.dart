import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../core/network/api_client.dart';

class AuthProvider extends ChangeNotifier {
  bool _isLoading = false;
  Map<String, dynamic>? _user;
  Map<String, dynamic>? _shop;
  String? _token;

  bool get isLoading => _isLoading;
  bool get isAuthenticated => _token != null;
  Map<String, dynamic>? get user => _user;
  Map<String, dynamic>? get shop => _shop;
  String get currencySymbol => _shop?['currency_symbol'] ?? '₹';

  String get userRole => (_user?['role'] ?? 'cashier').toString().toLowerCase();
  String get roleDisplayName {
    switch (userRole) {
      case 'super_admin':
        return 'Super Admin';
      case 'store_owner':
      case 'owner':
        return 'Store Owner';
      case 'manager':
        return 'Store Manager';
      case 'employee':
        return 'Staff Member';
      case 'cashier':
      default:
        return 'Cashier';
    }
  }

  bool get isOwnerOrAdmin => userRole == 'store_owner' || userRole == 'owner' || userRole == 'super_admin';
  bool get isManagerOrAbove => isOwnerOrAdmin || userRole == 'manager';

  void updateShop(Map<String, dynamic> newShop) {
    _shop = {...?_shop, ...newShop};
    notifyListeners();
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await ApiClient().dio.post('/auth/login', data: {
        'email': email,
        'password': password,
      });

      if (response.data['success'] == true) {
        _token = response.data['token'];
        _user = response.data['user'];
        _shop = response.data['shop'];

        ApiClient().setToken(_token);

        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', _token!);
        await prefs.setString('user_email', email);

        _isLoading = false;
        notifyListeners();
        return true;
      }
    } catch (e) {
      debugPrint('Login error: $e');
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }

  Future<void> checkAuth() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
    if (_token != null) {
      ApiClient().setToken(_token);
      try {
        final res = await ApiClient().dio.get('/auth/profile');
        if (res.data['success'] == true) {
          _user = res.data['user'];
          _shop = res.data['shop'];
        }
      } catch (e) {
        _token = null;
        await prefs.remove('auth_token');
      }
    }
    notifyListeners();
  }

  Future<void> logout() async {
    try {
      await ApiClient().dio.post('/auth/logout');
    } catch (_) {}

    _token = null;
    _user = null;
    _shop = null;
    ApiClient().setToken(null);

    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    notifyListeners();
  }
}
