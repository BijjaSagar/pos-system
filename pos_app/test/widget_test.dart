import 'package:flutter_test/flutter_test.dart';
import 'package:pos_app/main.dart';

void main() {
  testWidgets('POS SaaS App loads smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const PosSaaSApp());
    expect(find.byType(PosSaaSApp), findsOneWidget);
  });
}
