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
import javafx.scene.control.PasswordField;

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


    // Boutons de navigation
    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;

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

    // ==================== MÉTHODES DE NAVIGATION ====================

    @FXML
    private void handleHome() {
        naviguerVers("/fxml/MainMenu.fxml", "Path2Learn - Accueil");
    }

    @FXML
    private void handleCours() {
        naviguerVers("/fxml/CoursView.fxml", "Path2Learn - Cours");
    }

    @FXML
    private void handleRessources() {
        naviguerVers("/fxml/RessourcesView.fxml", "Path2Learn - Ressources");
    }

    @FXML
    private void handleQuestions() {
        naviguerVers("/fxml/QuestionListView.fxml", "Path2Learn - Quiz");
    }

    @FXML
    private void handleProjets() {
        naviguerVers("/fxml/PortfolioListView.fxml", "Path2Learn - Portfolios");
    }

    @FXML
    private void handleEvenements() {
        showInfoAlert("Événements", "Module Événements - Bientôt disponible");
    }

    @FXML
    private void handleUtilisateurs() {
        // Déjà sur la page des utilisateurs, juste rafraîchir
        loadUsers();
    }

    private void naviguerVers(String fxmlPath, String titre) {
        try {
            java.net.URL resource = getClass().getResource(fxmlPath);
            if (resource == null) {
                showAlert("Erreur", "Page non trouvée: " + fxmlPath);
                return;
            }
            FXMLLoader loader = new FXMLLoader(resource);
            Parent root = loader.load();
            Stage stage = (Stage) userTable.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle(titre);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger la page: " + e.getMessage());
        }
    }

    private void showInfoAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    // ==================== MÉTHODES CRUD ====================

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

    private void handleAjouterUser(ActionEvent event) {
        Optional<User> result = showUserDialog(null);
        result.ifPresent(user -> {
            try {
                user.setDate_creation(new Timestamp(System.currentTimeMillis()));
                serviceUser.ajouter(user);
                loadUsers();
                showSuccessAlert("Utilisateur ajouté avec succès !");
            } catch (SQLException e) {
                e.printStackTrace();
                showAlert("Erreur", "Impossible d'ajouter l'utilisateur !");
            }
        });
    }

    private void handleEdit(User user) {
        Optional<User> result = showUserDialog(user);
        result.ifPresent(updatedUser -> {
            try {
                updatedUser.setId(user.getId());
                serviceUser.modifier(updatedUser);
                loadUsers();
                showSuccessAlert("Utilisateur modifié avec succès !");
            } catch (SQLException e) {
                e.printStackTrace();
                showAlert("Erreur", "Impossible de modifier l'utilisateur !");
            }
        });
    }

    private void handleDelete(User user) {
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION,
                "Voulez-vous vraiment supprimer l'utilisateur " + user.getNom() + " " + user.getPrenom() + " ?",
                ButtonType.YES, ButtonType.NO);
        confirm.showAndWait().ifPresent(response -> {
            if (response == ButtonType.YES) {
                try {
                    serviceUser.supprimer(user.getId());
                    loadUsers();
                    showSuccessAlert("Utilisateur supprimé avec succès !");
                } catch (SQLException e) {
                    e.printStackTrace();
                    showAlert("Erreur", "Impossible de supprimer l'utilisateur !");
                }
            }
        });
    }

    private void showSuccessAlert(String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Succès");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private Optional<User> showUserDialog(User user) {

        Dialog<User> dialog = new Dialog<>();
        dialog.setTitle(user == null ? "Ajouter un utilisateur" : "Modifier un utilisateur");

        dialog.getDialogPane().getButtonTypes().addAll(ButtonType.OK, ButtonType.CANCEL);

        GridPane grid = new GridPane();
        grid.setHgap(10);
        grid.setVgap(10);
        grid.setStyle("-fx-padding: 20; -fx-alignment: center;");

        TextField nomField = new TextField();
        TextField prenomField = new TextField();
        TextField emailField = new TextField();
        PasswordField passwordField = new PasswordField();

        ComboBox<String> roleCombo = new ComboBox<>();
        roleCombo.getItems().addAll("ADMIN","STUDENT", "TEACHER");

        // ❌ IMPORTANT: hide status from FRONT
        ComboBox<String> statusCombo = new ComboBox<>();
        statusCombo.getItems().addAll("ENABLE", "DISABLE");

        nomField.setPromptText("Nom");
        prenomField.setPromptText("Prénom");
        emailField.setPromptText("Email");
        passwordField.setPromptText("Mot de passe");

        if (user != null) {
            nomField.setText(user.getNom());
            prenomField.setText(user.getPrenom());
            emailField.setText(user.getEmail());
            roleCombo.setValue(user.getRole());
            statusCombo.setValue(user.getStatus());
            passwordField.setText(user.getMot_de_passe());
        }

        grid.add(new Label("Nom:"), 0, 0);
        grid.add(nomField, 1, 0);

        grid.add(new Label("Prénom:"), 0, 1);
        grid.add(prenomField, 1, 1);

        grid.add(new Label("Email:"), 0, 2);
        grid.add(emailField, 1, 2);

        grid.add(new Label("Mot de passe:"), 0, 3);
        grid.add(passwordField, 1, 3);

        grid.add(new Label("Rôle:"), 0, 4);
        grid.add(roleCombo, 1, 4);

        grid.add(new Label("Statut:"), 0, 5);
        grid.add(statusCombo, 1, 5);

        dialog.getDialogPane().setContent(grid);

        dialog.setResultConverter(btn -> {
            if (btn == ButtonType.OK) {

                String nom = nomField.getText();
                String prenom = prenomField.getText();
                String email = emailField.getText();
                String password = passwordField.getText();
                String role = roleCombo.getValue();
                String status = statusCombo.getValue();

                StringBuilder errors = new StringBuilder();

                if (nom == null || nom.isBlank()) errors.append("Nom obligatoire\n");
                if (prenom == null || prenom.isBlank()) errors.append("Prénom obligatoire\n");

                if (email == null || email.isBlank())
                    errors.append("Email obligatoire\n");
                else if (!email.matches("^\\S+@\\S+\\.\\S+$"))
                    errors.append("Email invalide\n");

                if (password == null || password.length() < 6)
                    errors.append("Mot de passe >= 6 caractères\n");

                if (role == null)
                    errors.append("Rôle obligatoire\n");

                if (errors.length() > 0) {
                    showAlert("Erreur", errors.toString());
                    return null;
                }

                User u = new User();
                u.setNom(nom);
                u.setPrenom(prenom);
                u.setEmail(email);
                u.setRole(role);
                u.setStatus(status != null ? status : "ENABLE");
                u.setMot_de_passe(password);

                return u;
            }
            return null;
        });

        return dialog.showAndWait();
    }

    @FXML
    private void openBadgePage() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/Badge.fxml"));
            Parent root = loader.load();

            Stage stage = new Stage();
            stage.setTitle("Badge Page");
            stage.setScene(new Scene(root));
            stage.initModality(javafx.stage.Modality.WINDOW_MODAL);
            stage.initOwner(userTable.getScene().getWindow());
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            Alert alert = new Alert(Alert.AlertType.ERROR);
            alert.setTitle("Erreur");
            alert.setHeaderText("Impossible d'ouvrir la page des badges !");
            alert.setContentText(e.getMessage());
            alert.showAndWait();
        }
    }
}