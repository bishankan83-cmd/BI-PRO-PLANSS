import 'package:flutter/material.dart'; // Import the UI library

void main() {
  runApp(MyApp()); // This starts the app
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      home: Scaffold(
        appBar: AppBar(title: Text("My First App")), // The top bar
        body: Center(
          child: Text("Hello World!"), // The text in the middle
        ),
      ),
    );
  }
}