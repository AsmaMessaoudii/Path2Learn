package org.example.controllers;

import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.control.cell.TextFieldTableCell;
import javafx.scene.layout.VBox;
import javafx.stage.Stage;
import org.example.Models.Badge;
import org.example.Services.ServiceBadge;

import java.sql.SQLException;
import java.sql.Timestamp;
import java.time.LocalDateTime;
import java.util.Optional;

public class BadgeController {

    @FXML private TableView<Badge> badgeTable;
    @FXML private TableColumn<Badge, Integer> idColumn;
    @FXML private TableColumn<Badge, String> nameColumn;
    @FXML private TableColumn<Badge, String> descriptionColumn;
    @FXML private TableColumn<Badge, String> iconColumn;
    @FXML private TableColumn<Badge, Integer> requiredCoursesColumn;
    @FXML private TableColumn<Badge, Void> actionsColumn;

    @FXML private Button addBadgeBtn;

    private ServiceBadge serviceBadge;
    private ObservableList<Badge> badgeList;

    // Emojis valides pour les médailles
    private static final String[] VALID_ICONS = {
            "🥉", // Bronze
            "🥈", // Argent
            "🥇", // Or
            "💎", // Diamant
            "🏅", // Médaille générale
            "⭐", // Étoile
            "🌟", // Étoile brillante
            "🎖️", // Médaille militaire
            "🏆"  // Trophée
    };

    @FXML
    public void initialize() {
        serviceBadge = new ServiceBadge();
        setupTableColumns();
        loadBadges();
        setupTableEditing();
        setupAddButton();
        setupDeleteButtons();
    }

    private void setupTableColumns() {
        idColumn.setCellValueFactory(new PropertyValueFactory<>("id"));
        nameColumn.setCellValueFactory(new PropertyValueFactory<>("name"));
        descriptionColumn.setCellValueFactory(new PropertyValueFactory<>("description"));
        iconColumn.setCellValueFactory(new PropertyValueFactory<>("icon"));
        requiredCoursesColumn.setCellValueFactory(new PropertyValueFactory<>("required_courses"));
    }

    private void setupTableEditing() {
        // Édition du nom
        nameColumn.setCellFactory(TextFieldTableCell.forTableColumn());
        nameColumn.setOnEditCommit(event -> {
            Badge badge = event.getRowValue();
            String newName = event.getNewValue();
            if (validateName(newName)) {
                badge.setName(newName);
                updateBadge(badge);
            } else {
                showAlert("Erreur", "Nom invalide",
                        "Le nom ne peut pas être vide et doit contenir au moins 3 caractères.");
                loadBadges(); // Recharger pour annuler l'édition
            }
        });

        // Édition de la description
        descriptionColumn.setCellFactory(TextFieldTableCell.forTableColumn());
        descriptionColumn.setOnEditCommit(event -> {
            Badge badge = event.getRowValue();
            String newDescription = event.getNewValue();
            if (validateDescription(newDescription)) {
                badge.setDescription(newDescription);
                updateBadge(badge);
            } else {
                showAlert("Erreur", "Description invalide",
                        "La description ne peut pas être vide.");
                loadBadges();
            }
        });

        // Édition de l'icône
        iconColumn.setCellFactory(TextFieldTableCell.forTableColumn());
        iconColumn.setOnEditCommit(event -> {
            Badge badge = event.getRowValue();
            String newIcon = event.getNewValue();
            if (validateIcon(newIcon)) {
                badge.setIcon(newIcon);
                updateBadge(badge);
            } else {
                showAlert("Erreur", "Icône invalide",
                        "L'icône doit être un emoji valide (🥉, 🥈, 🥇, 💎, 🏅, ⭐, 🌟, 🎖️, 🏆)");
                loadBadges();
            }
        });

        // Édition du nombre de cours requis
        requiredCoursesColumn.setCellFactory(TextFieldTableCell.forTableColumn(new javafx.util.converter.IntegerStringConverter()));
        requiredCoursesColumn.setOnEditCommit(event -> {
            Badge badge = event.getRowValue();
            Integer newRequired = event.getNewValue();
            if (validateRequiredCourses(newRequired)) {
                badge.setRequired_courses(newRequired);
                updateBadge(badge);
            } else {
                showAlert("Erreur", "Nombre invalide",
                        "Le nombre de cours requis doit être un entier positif (≥ 0).");
                loadBadges();
            }
        });
    }

    private void setupAddButton() {
        addBadgeBtn.setOnAction(event -> showAddBadgeDialog());
    }

    private void setupDeleteButtons() {
        // Ajouter des boutons de suppression dans la colonne d'actions
        actionsColumn.setCellFactory(col -> new TableCell<Badge, Void>() {
            private final Button deleteBtn = new Button("🗑 Supprimer");

            {
                deleteBtn.setStyle("-fx-background-color: #ff6b6b; -fx-text-fill: white; -fx-font-size: 11px; -fx-padding: 5 10; -fx-background-radius: 5;");
                deleteBtn.setOnAction(event -> {
                    Badge badge = getTableView().getItems().get(getIndex());
                    deleteBadge(badge);
                });
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                if (empty) {
                    setGraphic(null);
                } else {
                    setGraphic(deleteBtn);
                }
            }
        });
    }

    private void showAddBadgeDialog() {
        Dialog<Badge> dialog = new Dialog<>();
        dialog.setTitle("Ajouter un badge");
        dialog.setHeaderText("Créer un nouveau badge");

        ButtonType addButtonType = new ButtonType("Ajouter", ButtonBar.ButtonData.OK_DONE);
        dialog.getDialogPane().getButtonTypes().addAll(addButtonType, ButtonType.CANCEL);

        // Création des champs
        VBox content = new VBox(10);
        content.setPadding(new Insets(20));

        TextField nameField = new TextField();
        nameField.setPromptText("Nom du badge*");

        TextArea descriptionArea = new TextArea();
        descriptionArea.setPromptText("Description*");
        descriptionArea.setPrefRowCount(3);

        ComboBox<String> iconCombo = new ComboBox<>(FXCollections.observableArrayList(VALID_ICONS));
        iconCombo.setPromptText("Icône (sélectionner un emoji)*");
        iconCombo.setEditable(true);

        TextField requiredField = new TextField();
        requiredField.setPromptText("Nombre de cours requis*");

        // Labels d'erreur
        Label nameError = new Label();
        nameError.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        Label descError = new Label();
        descError.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        Label iconError = new Label();
        iconError.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        Label requiredError = new Label();
        requiredError.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");

        content.getChildren().addAll(
                new Label("Nom :"), nameField, nameError,
                new Label("Description :"), descriptionArea, descError,
                new Label("Icône (🥉🥈🥇💎🏅⭐🌟🎖️🏆) :"), iconCombo, iconError,
                new Label("Cours requis :"), requiredField, requiredError
        );

        dialog.getDialogPane().setContent(content);

        // Désactiver le bouton Ajouter au début
        Button addButton = (Button) dialog.getDialogPane().lookupButton(addButtonType);
        addButton.setDisable(true);

        // Validation en temps réel
        Runnable validateFields = () -> {
            boolean isValid = validateName(nameField.getText())
                    && validateDescription(descriptionArea.getText())
                    && validateIcon(iconCombo.getValue())
                    && validateRequiredCoursesText(requiredField.getText());

            addButton.setDisable(!isValid);

            // Afficher/masquer les messages d'erreur
            nameError.setText(validateName(nameField.getText()) ? "✓" : "❌ Nom requis (min 3 caractères)");
            descError.setText(validateDescription(descriptionArea.getText()) ? "✓" : "❌ Description requise");
            iconError.setText(validateIcon(iconCombo.getValue()) ? "✓" : "❌ Sélectionnez un emoji valide");
            requiredError.setText(validateRequiredCoursesText(requiredField.getText()) ? "✓" : "❌ Entrez un nombre positif");

            // Changer la couleur des labels d'erreur
            nameError.setStyle(validateName(nameField.getText()) ? "-fx-text-fill: green; -fx-font-size: 11px;" : "-fx-text-fill: red; -fx-font-size: 11px;");
            descError.setStyle(validateDescription(descriptionArea.getText()) ? "-fx-text-fill: green; -fx-font-size: 11px;" : "-fx-text-fill: red; -fx-font-size: 11px;");
            iconError.setStyle(validateIcon(iconCombo.getValue()) ? "-fx-text-fill: green; -fx-font-size: 11px;" : "-fx-text-fill: red; -fx-font-size: 11px;");
            requiredError.setStyle(validateRequiredCoursesText(requiredField.getText()) ? "-fx-text-fill: green; -fx-font-size: 11px;" : "-fx-text-fill: red; -fx-font-size: 11px;");
        };

        nameField.textProperty().addListener((obs, old, val) -> validateFields.run());
        descriptionArea.textProperty().addListener((obs, old, val) -> validateFields.run());
        iconCombo.valueProperty().addListener((obs, old, val) -> validateFields.run());
        requiredField.textProperty().addListener((obs, old, val) -> validateFields.run());

        // Validation avant la fermeture
        dialog.setResultConverter(dialogButton -> {
            if (dialogButton == addButtonType && !addButton.isDisable()) {
                String name = nameField.getText().trim();
                String description = descriptionArea.getText().trim();
                String icon = iconCombo.getValue().trim();
                int requiredCourses = Integer.parseInt(requiredField.getText().trim());

                return new Badge(name, description, icon, requiredCourses,
                        Timestamp.valueOf(LocalDateTime.now()));
            }
            return null;
        });

        Optional<Badge> result = dialog.showAndWait();
        result.ifPresent(badge -> {
            try {
                serviceBadge.ajouter(badge);
                loadBadges();
                showAlert("Succès", "Badge ajouté", "Le badge \"" + badge.getName() + "\" a été créé.");
            } catch (SQLException e) {
                showAlert("Erreur", "Impossible d'ajouter le badge", e.getMessage());
                e.printStackTrace();
            }
        });
    }

    // ══════════════════════════════════════════
    //  MÉTHODES DE VALIDATION
    // ══════════════════════════════════════════

    private boolean validateName(String name) {
        return name != null && !name.trim().isEmpty() && name.trim().length() >= 3;
    }

    private boolean validateDescription(String description) {
        return description != null && !description.trim().isEmpty();
    }

    private boolean validateIcon(String icon) {
        if (icon == null || icon.trim().isEmpty()) return false;
        for (String validIcon : VALID_ICONS) {
            if (validIcon.equals(icon.trim())) {
                return true;
            }
        }
        return false;
    }

    private boolean validateRequiredCourses(Integer required) {
        return required != null && required >= 0;
    }

    private boolean validateRequiredCoursesText(String requiredText) {
        if (requiredText == null || requiredText.trim().isEmpty()) return false;
        try {
            int value = Integer.parseInt(requiredText.trim());
            return value >= 0;
        } catch (NumberFormatException e) {
            return false;
        }
    }

    // ══════════════════════════════════════════
    //  MÉTHODES CRUD
    // ══════════════════════════════════════════

    private void loadBadges() {
        try {
            badgeList = FXCollections.observableArrayList(serviceBadge.recuperer());
            badgeTable.setItems(badgeList);
        } catch (SQLException e) {
            showAlert("Erreur", "Impossible de charger les badges", e.getMessage());
            e.printStackTrace();
        }
    }

    private void updateBadge(Badge badge) {
        try {
            serviceBadge.modifier(badge);
            showAlert("Succès", "Badge modifié", "Le badge a été mis à jour.");
        } catch (SQLException e) {
            showAlert("Erreur", "Impossible de modifier le badge", e.getMessage());
            e.printStackTrace();
            loadBadges(); // Recharger en cas d'erreur
        }
    }

    private void deleteBadge(Badge badge) {
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Confirmation");
        confirm.setHeaderText("Supprimer le badge");
        confirm.setContentText("Êtes-vous sûr de vouloir supprimer le badge \"" + badge.getName() + "\" ?");

        Optional<ButtonType> result = confirm.showAndWait();
        if (result.isPresent() && result.get() == ButtonType.OK) {
            try {
                serviceBadge.supprimer(badge.getId());
                loadBadges();
                showAlert("Succès", "Badge supprimé", "Le badge a été supprimé.");
            } catch (SQLException e) {
                showAlert("Erreur", "Impossible de supprimer le badge", e.getMessage());
                e.printStackTrace();
            }
        }
    }

    @FXML
    private void goBackToUsers() {
        Stage stage = (Stage) badgeTable.getScene().getWindow();
        stage.close();
    }

    private void showAlert(String title, String header, String content) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(header);
        alert.setContentText(content);
        alert.showAndWait();
    }
}