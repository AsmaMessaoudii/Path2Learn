package org.example.controllers;

import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.GridPane;
import javafx.scene.layout.HBox;
import javafx.stage.Stage;
import org.example.Models.User;
import org.example.Services.ServiceUser;

import java.io.IOException;
import java.sql.SQLException;
import java.sql.Timestamp;
import java.util.List;
import java.util.Optional;

public class UserController {

    @FXML private TableView<User> userTable;
    @FXML private TableColumn<User, Integer> idColumn;
    @FXML private TableColumn<User, String> nomColumn;
    @FXML private TableColumn<User, String> prenomColumn;
    @FXML private TableColumn<User, String> emailColumn;
    @FXML private TableColumn<User, String> roleColumn;
    @FXML private TableColumn<User, String> statusColumn;
    @FXML private TableColumn<User, Void> actionsColumn;

    @FXML private Button addUserBtn;

    @FXML private Button badgeBtn;

    private ServiceUser serviceUser = new ServiceUser();
    private ObservableList<User> users = FXCollections.observableArrayList();


    @FXML
    public void initialize() {
        // Bind table columns
        idColumn.setCellValueFactory(new PropertyValueFactory<>("id"));
        nomColumn.setCellValueFactory(new PropertyValueFactory<>("nom"));
        prenomColumn.setCellValueFactory(new PropertyValueFactory<>("prenom"));
        emailColumn.setCellValueFactory(new PropertyValueFactory<>("email"));
        roleColumn.setCellValueFactory(new PropertyValueFactory<>("role"));
        statusColumn.setCellValueFactory(new PropertyValueFactory<>("status"));

        // Load users
        loadUsers();

        // Add CRUD buttons
        addActionButtons();


        // Add user button
        addUserBtn.setOnAction(this::handleAjouterUser);

        badgeBtn.setOnAction(e -> openBadgePage());
    }

    private void loadUsers() {
        try {
            List<User> list = serviceUser.recuperer();
            users.setAll(list);
            userTable.setItems(users);
        } catch (SQLException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger les utilisateurs !");
        }
    }

    private void addActionButtons() {
        actionsColumn.setCellFactory(param -> new TableCell<>() {
            private final Button editBtn = new Button("Modifier");
            private final Button deleteBtn = new Button("Supprimer");
            private final HBox pane = new HBox(editBtn, deleteBtn);

            {
                pane.setSpacing(10);
                editBtn.setStyle("-fx-background-color: #81C784; -fx-text-fill: white; -fx-font-size: 12px; -fx-background-radius: 20;");
                deleteBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #FF5252; -fx-border-color: #FF5252; -fx-border-radius: 20; -fx-background-radius: 20;");

                editBtn.setOnAction(event -> handleEdit(getTableView().getItems().get(getIndex())));
                deleteBtn.setOnAction(event -> handleDelete(getTableView().getItems().get(getIndex())));
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                setGraphic(empty ? null : pane);
            }
        });
    }

    // --- ADD USER ---
    private void handleAjouterUser(ActionEvent event) {
        Optional<User> result = showUserDialog(null);
        result.ifPresent(user -> {
            try {
                user.setDate_creation(new Timestamp(System.currentTimeMillis()));
                serviceUser.ajouter(user);
                loadUsers();
            } catch (SQLException e) {
                e.printStackTrace();
                showAlert("Erreur", "Impossible d'ajouter l'utilisateur !");
            }
        });
    }

    // --- EDIT USER ---
    private void handleEdit(User user) {
        Optional<User> result = showUserDialog(user);
        result.ifPresent(updatedUser -> {
            try {
                updatedUser.setId(user.getId()); // keep the same ID
                serviceUser.modifier(updatedUser);
                loadUsers();
            } catch (SQLException e) {
                e.printStackTrace();
                showAlert("Erreur", "Impossible de modifier l'utilisateur !");
            }
        });
    }

    // --- DELETE USER ---
    private void handleDelete(User user) {
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION,
                "Voulez-vous vraiment supprimer l'utilisateur " + user.getNom() + " ?", ButtonType.YES, ButtonType.NO);
        confirm.showAndWait().ifPresent(response -> {
            if (response == ButtonType.YES) {
                try {
                    serviceUser.supprimer(user.getId());
                    loadUsers();
                } catch (SQLException e) {
                    e.printStackTrace();
                    showAlert("Erreur", "Impossible de supprimer l'utilisateur !");
                }
            }
        });
    }

    // --- ALERT ---
    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    // --- USER DIALOG WITH VALIDATION ---
    private Optional<User> showUserDialog(User user) {
        Dialog<User> dialog = new Dialog<>();
        dialog.setTitle(user == null ? "Ajouter un utilisateur" : "Modifier un utilisateur");
        dialog.getDialogPane().getButtonTypes().addAll(ButtonType.OK, ButtonType.CANCEL);

        GridPane grid = new GridPane();
        grid.setHgap(10);
        grid.setVgap(10);

        TextField nomField = new TextField();
        nomField.setPromptText("Nom");
        TextField prenomField = new TextField();
        prenomField.setPromptText("Prénom");
        TextField emailField = new TextField();
        emailField.setPromptText("Email");

        ComboBox<String> roleCombo = new ComboBox<>();
        roleCombo.getItems().addAll("TEACHER", "STUDENT", "ADMIN");
        roleCombo.setPromptText("Rôle");

        ComboBox<String> statusCombo = new ComboBox<>();
        statusCombo.getItems().addAll("ENABLE", "DISABLE");
        statusCombo.setPromptText("Statut");

        if (user != null) {
            nomField.setText(user.getNom());
            prenomField.setText(user.getPrenom());
            emailField.setText(user.getEmail());
            roleCombo.setValue(user.getRole());
            statusCombo.setValue(user.getStatus());
        }

        grid.add(new Label("Nom:"), 0, 0);
        grid.add(nomField, 1, 0);
        grid.add(new Label("Prénom:"), 0, 1);
        grid.add(prenomField, 1, 1);
        grid.add(new Label("Email:"), 0, 2);
        grid.add(emailField, 1, 2);
        grid.add(new Label("Rôle:"), 0, 3);
        grid.add(roleCombo, 1, 3);
        grid.add(new Label("Statut:"), 0, 4);
        grid.add(statusCombo, 1, 4);

        dialog.getDialogPane().setContent(grid);

        dialog.setResultConverter(dialogButton -> {
            if (dialogButton == ButtonType.OK) {

                String nom = nomField.getText();
                String prenom = prenomField.getText();
                String email = emailField.getText();
                String role = roleCombo.getValue();
                String status = statusCombo.getValue();

                StringBuilder errors = new StringBuilder();

                // Empty fields
                if (nom == null || nom.isBlank()) errors.append("Nom est obligatoire.\n");
                if (prenom == null || prenom.isBlank()) errors.append("Prénom est obligatoire.\n");
                if (email == null || email.isBlank()) errors.append("Email est obligatoire.\n");
                else if (!email.matches("^\\S+@\\S+\\.\\S+$")) errors.append("Email invalide.\n");

                // Role enum
                if (role == null || (!role.equalsIgnoreCase("TEACHER") &&
                        !role.equalsIgnoreCase("STUDENT") && !role.equalsIgnoreCase("ADMIN")))
                    errors.append("Rôle doit être TEACHER, STUDENT ou ADMIN.\n");

                // Status enum
                if (status == null || (!status.equalsIgnoreCase("ENABLE") &&
                        !status.equalsIgnoreCase("DISABLE")))
                    errors.append("Statut doit être ENABLE ou DISABLE.\n");

                if (errors.length() > 0) {
                    showAlert("Erreur de validation", errors.toString());
                    return null; // cancel OK result if validation fails
                }

                User u = new User();
                u.setNom(nom);
                u.setPrenom(prenom);
                u.setEmail(email);
                u.setRole(role.toUpperCase());
                u.setStatus(status.toUpperCase());
                u.setMot_de_passe("default123"); // placeholder password
                return u;
            }
            return null;
        });

        return dialog.showAndWait();
    }
    @FXML
    private void openBadgePage() {
        try {
            // Notice the leading slash and correct folder
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/Badge.fxml"));
            Parent root = loader.load();

            Stage stage = new Stage();
            stage.setTitle("Badge Page");
            stage.setScene(new Scene(root));
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            // Optional: show alert to the user
            Alert alert = new Alert(Alert.AlertType.ERROR);
            alert.setTitle("Erreur");
            alert.setHeaderText("Impossible d'ouvrir la page des badges !");
            alert.setContentText(e.getMessage());
            alert.showAndWait();
        }
    }

}