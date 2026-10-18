import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'core/constants/app_colors.dart';
import 'providers/auth_provider.dart';
import 'providers/cart_provider.dart';
import 'providers/printer_provider.dart';
import 'screens/auth/login_screen.dart';
import 'screens/pos/pos_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const PosSaaSApp());
}

class PosSaaSApp extends StatelessWidget {
  const PosSaaSApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()..checkAuth()),
        ChangeNotifierProvider(create: (_) => CartProvider()),
        ChangeNotifierProvider(create: (_) => PrinterProvider()),
      ],
      child: MaterialApp(
        title: 'POS SAAS',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          useMaterial3: true,
          colorScheme: ColorScheme.fromSeed(
            seedColor: AppColors.primary,
            primary: AppColors.primary,
            surface: AppColors.surface,
          ),
          scaffoldBackgroundColor: AppColors.background,
          textTheme: GoogleFonts.interTextTheme(Theme.of(context).textTheme),
          appBarTheme: const AppBarTheme(
            elevation: 0,
            centerTitle: false,
          ),
        ),
        home: const AuthGate(),
      ),
    );
  }
}

class AuthGate extends StatelessWidget {
  const AuthGate({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    if (auth.isAuthenticated) {
      return const PosScreen();
    }
    return const LoginScreen();
  }
}
