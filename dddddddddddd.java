import java.awt.*;
import java.sql.*;
import javax.swing.*;

public class HotelManagementSystem {
    // Database Connection Details
    private static final String DB_URL = "jdbc:mysql://localhost:3306/hotel_management";
    private static final String DB_USERNAME = "root";
    private static final String DB_PASSWORD = "root";
    private JFrame frame;

    public static void main(String[] args) {
        SwingUtilities.invokeLater(() -> {
            HotelManagementSystem app = new HotelManagementSystem();
            app.checkDatabaseConnection();
            app.showLoginScreen();
        });
    }

    // Method to check database connection
    private void checkDatabaseConnection() {
        try (Connection conn = DriverManager.getConnection(DB_URL, DB_USERNAME, DB_PASSWORD)) {
            if (conn != null) {
                JOptionPane.showMessageDialog(null, "Database connection successful!", "Success", JOptionPane.INFORMATION_MESSAGE);
            }
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(null, "Database connection failed. Error: " + e.getMessage(), "Error", JOptionPane.ERROR_MESSAGE);
            e.printStackTrace();
        }
    }
    // Method to display the login screen
    private void showLoginScreen() {
        frame = new JFrame("Hotel Management System - Login");
        frame.setSize(450, 300);
        frame.setLayout(new BorderLayout());

        // Create a panel with a background image
        JPanel backgroundPanel = new JPanel() {
            @Override
            protected void paintComponent(Graphics g) {
                super.paintComponent(g);
                // Load and draw the background image
                ImageIcon backgroundImage = new ImageIcon("src/resources/background.jpg"); // Replace with your image path
                g.drawImage(backgroundImage.getImage(), 0, 0, getWidth(), getHeight(), this);
            }
        };
        backgroundPanel.setLayout(new BorderLayout());

        // Create the main login panel with GridBagLayout for flexible and aligned design
        JPanel loginPanel = new JPanel(new GridBagLayout());
        loginPanel.setOpaque(false);  // Make the panel transparent to see the background
        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(10, 10, 10, 10);  // Padding between components

        // Components
        JLabel lblUsername = new JLabel("Username:");
        JLabel lblPassword = new JLabel("Password:");
        JTextField txtUsername = new JTextField(20);
        JPasswordField txtPassword = new JPasswordField(20);
        JButton btnLogin = new JButton("Login");

        // Label customizations
        lblUsername.setFont(new Font("Arial", Font.BOLD, 14));
        lblUsername.setForeground(Color.black);
        lblPassword.setFont(new Font("Arial", Font.BOLD, 14));
        lblPassword.setForeground(Color.black);

        // Text field customizations
        txtUsername.setFont(new Font("Arial", Font.PLAIN, 14));
        txtPassword.setFont(new Font("Arial", Font.PLAIN, 14));
        txtUsername.setBorder(BorderFactory.createLineBorder(new Color(70, 130, 180), 2));
        txtPassword.setBorder(BorderFactory.createLineBorder(new Color(70, 130, 180), 2));
        txtUsername.setPreferredSize(new Dimension(250, 30));
        txtPassword.setPreferredSize(new Dimension(250, 30));

        // Button customizations
        btnLogin.setBackground(new Color(70, 130, 180));
        btnLogin.setForeground(Color.WHITE);
        btnLogin.setFont(new Font("Arial", Font.BOLD, 14));
        btnLogin.setPreferredSize(new Dimension(250, 40));
        btnLogin.setFocusPainted(false);
        btnLogin.setCursor(new Cursor(Cursor.HAND_CURSOR));
        btnLogin.setBorder(BorderFactory.createEmptyBorder());

        // Align components in the grid
        gbc.gridx = 0;
        gbc.gridy = 0;
        loginPanel.add(lblUsername, gbc);

        gbc.gridx = 1;
        gbc.gridy = 0;
        loginPanel.add(txtUsername, gbc);

        gbc.gridx = 0;
        gbc.gridy = 1;
        loginPanel.add(lblPassword, gbc);

        gbc.gridx = 1;
        gbc.gridy = 1;
        loginPanel.add(txtPassword, gbc);

        gbc.gridx = 1;
        gbc.gridy = 2;
        loginPanel.add(btnLogin, gbc);

        // Add the login panel to the background panel
        backgroundPanel.add(loginPanel, BorderLayout.CENTER);

        // Setting the window icon
        ImageIcon loginIcon = new ImageIcon("src/resources/download.png");  // Adjust path accordingly
        frame.setIconImage(loginIcon.getImage());

        // Action listener for login button
        btnLogin.addActionListener(e -> {
            String username = txtUsername.getText();
            String password = new String(txtPassword.getPassword());
            if (authenticate(username, password)) {
                JOptionPane.showMessageDialog(frame, "Login successful!", "Success", JOptionPane.INFORMATION_MESSAGE);
                frame.dispose();
                showDashboard();
            } else {
                JOptionPane.showMessageDialog(frame, "Invalid credentials.", "Error", JOptionPane.ERROR_MESSAGE);
            }
        });

        // Set window properties
        frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        frame.setLocationRelativeTo(null);  // Center the window
        frame.add(backgroundPanel);
        frame.setVisible(true);
    }
    // Method to authenticate the login credentials
    private boolean authenticate(String username, String password) {
        String query = "SELECT * FROM users WHERE username = ? AND password = ?";
        try (Connection conn = DriverManager.getConnection(DB_URL, DB_USERNAME, DB_PASSWORD);
             PreparedStatement stmt = conn.prepareStatement(query)) {

            stmt.setString(1, username);
            stmt.setString(2, password);
            ResultSet rs = stmt.executeQuery();
            return rs.next();
        } catch (SQLException e) {
            e.printStackTrace();
            return false;
        }
    }

    // Method to show the dashboard screen after successful login
    private void showDashboard() {
        JFrame dashboard = new JFrame("Hotel Management System - Dashboard");
        dashboard.setSize(600, 400);
        dashboard.setLayout(new GridLayout(3, 2, 10, 10));
        dashboard.getContentPane().setBackground(new Color(230, 230, 250)); // Lavender background color

        JButton btnRegisterCustomer = new JButton("Register Customer");
        JButton btnViewRooms = new JButton("View Room Status");
        JButton btnBookRoom = new JButton("Book Room");
        JButton btnCheckOut = new JButton("Check Out");
        JButton btnViewReports = new JButton("View Reports");
        JButton btnExit = new JButton("Exit");

        styleButton(btnRegisterCustomer);
        styleButton(btnViewRooms);
        styleButton(btnBookRoom);
        styleButton(btnCheckOut);
        styleButton(btnViewReports);
        styleButton(btnExit);

        btnRegisterCustomer.addActionListener(e -> registerCustomer());
        btnViewRooms.addActionListener(e -> viewRoomStatus());
        btnBookRoom.addActionListener(e -> bookRoom());
        btnCheckOut.addActionListener(e -> checkOutRoom());
        btnViewReports.addActionListener(e -> viewReports());
        btnExit.addActionListener(e -> System.exit(0));

        dashboard.add(btnRegisterCustomer);
        dashboard.add(btnViewRooms);
        dashboard.add(btnBookRoom);
        dashboard.add(btnCheckOut);
        dashboard.add(btnViewReports);
        dashboard.add(btnExit);

        dashboard.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        dashboard.setVisible(true);
    }

    // Method to style buttons for the dashboard
    private void styleButton(JButton button) {
        button.setBackground(new Color(70, 130, 180));  // Steel blue background
        button.setForeground(Color.WHITE);  // White text
        button.setFont(new Font("Arial", Font.BOLD, 16));
        button.setFocusPainted(false);  // Remove focus border

        button.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseEntered(java.awt.event.MouseEvent evt) {
                button.setBackground(new Color(100, 149, 237));  // Lighter blue on hover
            }
            public void mouseExited(java.awt.event.MouseEvent evt) {
                button.setBackground(new Color(70, 130, 180));  // Reset to original color
            }
        });
    }

    // Method to register a new customer
    private void registerCustomer() {
        String customerName = JOptionPane.showInputDialog("Enter Customer Name:");
        String email = JOptionPane.showInputDialog("Enter Email:");
        String phone = JOptionPane.showInputDialog("Enter Phone Number:");

        try (Connection conn = DriverManager.getConnection(DB_URL, DB_USERNAME, DB_PASSWORD)) {
            String query = "INSERT INTO users (username, password, full_name, email, phone_number, role) VALUES (?, ?, ?, ?, ?, ?)";
            PreparedStatement stmt = conn.prepareStatement(query);
            stmt.setString(1, customerName.toLowerCase());
            stmt.setString(2, "defaultpassword"); // Default password for customer
            stmt.setString(3, customerName);
            stmt.setString(4, email);
            stmt.setString(5, phone);
            stmt.setString(6, "customer");

            stmt.executeUpdate();
            JOptionPane.showMessageDialog(frame, "Customer registered successfully!", "Success", JOptionPane.INFORMATION_MESSAGE);
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    // Method to view all rooms with their occupancy status
    private void viewRoomStatus() {
        StringBuilder roomStatus = new StringBuilder("Room Status:\n");

        try (Connection conn = DriverManager.getConnection(DB_URL, DB_USERNAME, DB_PASSWORD);
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery("SELECT * FROM rooms")) {

            while (rs.next()) {
                roomStatus.append("Room ").append(rs.getInt("room_number"))
                        .append(": ").append(rs.getBoolean("is_occupied") ? "Occupied" : "Vacant")
                        .append("\n");
            }

            JOptionPane.showMessageDialog(frame, roomStatus.toString(), "Room Status", JOptionPane.INFORMATION_MESSAGE);
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    // Method to book a room for a customer
    private void bookRoom() {
        String roomNumber = JOptionPane.showInputDialog("Enter Room Number:");
        String customerName = JOptionPane.showInputDialog("Enter Customer Name:");

        try (Connection conn = DriverManager.getConnection(DB_URL, DB_USERNAME, DB_PASSWORD)) {
            String query = "UPDATE rooms SET is_occupied = TRUE, customer_name = ? WHERE room_number = ? AND is_occupied = FALSE";
            PreparedStatement stmt = conn.prepareStatement(query);
            stmt.setString(1, customerName);
            stmt.setInt(2, Integer.parseInt(roomNumber));

            int rowsUpdated = stmt.executeUpdate();
            if (rowsUpdated > 0) {
                JOptionPane.showMessageDialog(frame, "Room booked successfully!", "Success", JOptionPane.INFORMATION_MESSAGE);
            } else {
                JOptionPane.showMessageDialog(frame, "Room is already occupied or invalid room number.", "Error", JOptionPane.ERROR_MESSAGE);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    // Method to check out a room
    private void checkOutRoom() {
        String roomNumber = JOptionPane.showInputDialog("Enter Room Number:");

        try (Connection conn = DriverManager.getConnection(DB_URL, DB_USERNAME, DB_PASSWORD)) {
            String query = "UPDATE rooms SET is_occupied = FALSE, customer_name = NULL WHERE room_number = ? AND is_occupied = TRUE";
            PreparedStatement stmt = conn.prepareStatement(query);
            stmt.setInt(1, Integer.parseInt(roomNumber));

            int rowsUpdated = stmt.executeUpdate();
            if (rowsUpdated > 0) {
                JOptionPane.showMessageDialog(frame, "Room checked out successfully!", "Success", JOptionPane.INFORMATION_MESSAGE);
            } else {
                JOptionPane.showMessageDialog(frame, "Room is not occupied or invalid room number.", "Error", JOptionPane.ERROR_MESSAGE);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    // Method to view occupancy reports
    private void viewReports() {
        StringBuilder report = new StringBuilder("Occupancy Rate Report:\n");

        try (Connection conn = DriverManager.getConnection(DB_URL, DB_USERNAME, DB_PASSWORD);
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery("SELECT COUNT(*) FROM rooms WHERE is_occupied = TRUE")) {

            if (rs.next()) {
                int occupiedRooms = rs.getInt(1);
                ResultSet totalRoomsRs = stmt.executeQuery("SELECT COUNT(*) FROM rooms");
                if (totalRoomsRs.next()) {
                    int totalRooms = totalRoomsRs.getInt(1);
                    double occupancyRate = ((double) occupiedRooms / totalRooms) * 100;
                    report.append("Occupied Rooms: ").append(occupiedRooms).append("\n")
                            .append("Total Rooms: ").append(totalRooms).append("\n")
                            .append("Occupancy Rate: ").append(String.format("%.2f", occupancyRate)).append("%\n");
                }
            }
            JOptionPane.showMessageDialog(frame, report.toString(), "Occupancy Rate Report", JOptionPane.INFORMATION_MESSAGE);
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}