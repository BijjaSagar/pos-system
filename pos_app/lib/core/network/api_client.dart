import 'dart:io';
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiClient {
  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;

  late Dio dio;
  String? _token;

  ApiClient._internal() {
    // Android emulator uses 10.0.2.2 to access host machine, iOS/desktop uses localhost
    String defaultBase = Platform.isAndroid 
        ? 'http://10.0.2.2:8000/api' 
        : 'http://127.0.0.1:8000/api';

    dio = Dio(BaseOptions(
      baseUrl: defaultBase,
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 15),
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
