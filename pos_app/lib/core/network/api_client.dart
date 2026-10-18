import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiClient {
  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;

  late Dio dio;
  String? _token;

  ApiClient._internal() {
    // Production Cloud URL for deployed POS SaaS backend on Hostinger
    const String defaultBase = 'https://vaakai.app/api';

    dio = Dio(BaseOptions(
      baseUrl: defaultBase,
      connectTimeout: const Duration(seconds: 20),
      receiveTimeout: const Duration(seconds: 20),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ));

    dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        if (_token != null) {
          options.headers['Authorization'] = 'Bearer $_token';
        } else {
          final prefs = await SharedPreferences.getInstance();
          _token = prefs.getString('auth_token');
          if (_token != null) {
            options.headers['Authorization'] = 'Bearer $_token';
          }
        }
        return handler.next(options);
      },
      onError: (DioException e, handler) {
        return handler.next(e);
      },
    ));
  }

  void setBaseUrl(String url) {
    dio.options.baseUrl = url.endsWith('/') ? '${url}api' : '$url/api';
  }

  void setToken(String? token) {
    _token = token;
  }
}
