package org.example.controllers;

import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import org.example.Models.Question;
import org.example.Services.QuestionService;
import org.example.validators.QuestionValidator;
import java.io.IOException;
import java.sql.Date;
import java.sql.SQLException;
import java.time.LocalDate;

public class QuestionAddController {

    @FXML private TextField titreField;
    @FXML private TextArea descriptionArea;
    @FXML private TextField dureeField;
    @FXML private TextField noteMaxField;
    @FXML private TextField userIdField;
    @FXML private DatePicker datePicker;
    @FXML private Label statusLabel;
    @FXML private Label titreErrorLabel;
    @FXML private Label descriptionErrorLabel;
    @FXML private Label dureeErrorLabel;
    @FXML private Label noteMaxErrorLabel;
    @FXML private Label userIdErrorLabel;
    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;

    private QuestionService questionService;
    private QuestionValidator validator;

    @FXML
    public void initialize() {
        questionService = new QuestionService();
        validator = new QuestionValidator();
        datePicker.setValue(LocalDate.now());
        setupValidationListeners();
    }

    private void setupValidationListeners() {
        titreField.textProperty().addListener((obs, oldVal, newVal) -> validateTitre());
        descriptionArea.textProperty().addListener((obs, oldVal, newVal) -> validateDescription());
        dureeField.textProperty().addListener((obs, oldVal, newVal) -> validateDuree());
        noteMaxField.textProperty().addListener((obs, oldVal, newVal) -> validateNoteMax());
        userIdField.textProperty().addListener((obs, oldVal, newVal) -> validateUserId());
    }

    private void validateTitre() {
        String titre = titreField.getText();
        if (titre == null || titre.trim().isEmpty()) {
            titreErrorLabel.setText("❌ Le titre est obligatoire");
            titreErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        } else if (titre.length() < 3) {
            titreErrorLabel.setText("❌ Le titre doit contenir au moins 3 caractères");
            titreErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        } else if (titre.length() > 200) {
            titreErrorLabel.setText("❌ Le titre ne doit pas dépasser 200 caractères");
            titreErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        } else if (!titre.matches("^[a-zA-Z0-9\\s\\p{L}À-ÿ\\-\\'\\?\\!\\,\\.]+$")) {
            titreErrorLabel.setText("❌ Le titre contient des caractères non autorisés");
            titreErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        } else {
            titreErrorLabel.setText("✅ Titre valide");
            titreErrorLabel.setStyle("-fx-text-fill: green; -fx-font-size: 11px;");
        }
    }

    private void validateDescription() {
        String description = descriptionArea.getText();
        if (description == null || description.trim().isEmpty()) {
            descriptionErrorLabel.setText("❌ La description est obligatoire");
            descriptionErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        } else if (description.length() < 10) {
            descriptionErrorLabel.setText("❌ La description doit contenir au moins 10 caractères");
            descriptionErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        } else if (description.length() > 1000) {
            descriptionErrorLabel.setText("❌ La description ne doit pas dépasser 1000 caractères");
            descriptionErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        } else {
            descriptionErrorLabel.setText("✅ Description valide");
            descriptionErrorLabel.setStyle("-fx-text-fill: green; -fx-font-size: 11px;");
        }
    }

    private void validateDuree() {
        String dureeText = dureeField.getText();
        if (dureeText == null || dureeText.trim().isEmpty()) {
            dureeErrorLabel.setText("❌ La durée est obligatoire");
            dureeErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
            return;
        }
        try {
            int duree = Integer.parseInt(dureeText);
            if (duree <= 0) {
                dureeErrorLabel.setText("❌ La durée doit être supérieure à 0");
                dureeErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
            } else if (duree > 360) {
                dureeErrorLabel.setText("❌ La durée ne peut pas dépasser 360 minutes");
                dureeErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
            } else {
                dureeErrorLabel.setText("✅ Durée valide");
                dureeErrorLabel.setStyle("-fx-text-fill: green; -fx-font-size: 11px;");
            }
        } catch (NumberFormatException e) {
            dureeErrorLabel.setText("❌ Veuillez entrer un nombre valide");
            dureeErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        }
    }

    private void validateNoteMax() {
        String noteText = noteMaxField.getText();
        if (noteText == null || noteText.trim().isEmpty()) {
            noteMaxErrorLabel.setText("❌ La note maximale est obligatoire");
            noteMaxErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
            return;
        }
        try {
            float noteMax = Float.parseFloat(noteText);
            if (noteMax <= 0) {
                noteMaxErrorLabel.setText("❌ La note doit être supérieure à 0");
                noteMaxErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
            } else if (noteMax > 100) {
                noteMaxErrorLabel.setText("❌ La note ne peut pas dépasser 100");
                noteMaxErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
            } else {
                noteMaxErrorLabel.setText("✅ Note valide");
                noteMaxErrorLabel.setStyle("-fx-text-fill: green; -fx-font-size: 11px");
            }
        } catch (NumberFormatException e) {
            noteMaxErrorLabel.setText("❌ Veuillez entrer un nombre valide");
            noteMaxErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        }
    }

    private void validateUserId() {
        String userIdText = userIdField.getText();
        if (userIdText == null || userIdText.trim().isEmpty()) {
            userIdErrorLabel.setText("❌ L'ID utilisateur est obligatoire");
            userIdErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
            return;
        }
        try {
            int userId = Integer.parseInt(userIdText);
            if (userId <= 0) {
                userIdErrorLabel.setText("❌ L'ID doit être un nombre positif");
                userIdErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
            } else {
                userIdErrorLabel.setText("✅ ID valide");
                userIdErrorLabel.setStyle("-fx-text-fill: green; -fx-font-size: 11px;");
            }
        } catch (NumberFormatException e) {
            userIdErrorLabel.setText("❌ Veuillez entrer un nombre valide");
            userIdErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        }
    }

    @FXML
    private void handleAjouter(ActionEvent event) {
        validateTitre();
        validateDescription();
        validateDuree();
        validateNoteMax();
        validateUserId();

        Question question = new Question();
        question.setTitre(titreField.getText());
        question.setDescription(descriptionArea.getText());
        question.setDateCreation(Date.valueOf(datePicker.getValue()));

        try {
            question.setDuree(Integer.parseInt(dureeField.getText()));
            question.setNoteMax(Float.parseFloat(noteMaxField.getText()));
            question.setUserId(Integer.parseInt(userIdField.getText()));
        } catch (NumberFormatException e) {
            showError("Veuillez vérifier les champs numériques");
            return;
        }

        if (!validator.validate(question)) {
            showError("Erreurs de validation:\n• " + validator.getErrorsAsString());
            return;
        }

        try {
            questionService.ajouter(question);
            Alert alert = new Alert(Alert.AlertType.INFORMATION);
            alert.setTitle("Succès");
            alert.setHeaderText(null);
            alert.setContentText("Question ajoutée avec succès !");
            alert.showAndWait();
            handleRetour(event);
        } catch (SQLException e) {
            showError("Erreur lors de l'ajout: " + e.getMessage());
        }
    }

    @FXML
    private void handleAnnuler(ActionEvent event) {
        clearForm();
    }

    @FXML
    private void handleRetour(ActionEvent event) {
        navigateTo("/fxml/QuestionListView.fxml");
    }

    private void clearForm() {
        titreField.clear();
        descriptionArea.clear();
        dureeField.clear();
        noteMaxField.clear();
        userIdField.clear();
        datePicker.setValue(LocalDate.now());
        titreErrorLabel.setText("");
        descriptionErrorLabel.setText("");
        dureeErrorLabel.setText("");
        noteMaxErrorLabel.setText("");
        userIdErrorLabel.setText("");
        statusLabel.setText("Formulaire réinitialisé");
        statusLabel.setStyle("-fx-text-fill: blue;");
    }

    private void showError(String message) {
        statusLabel.setText("❌ " + message);
        statusLabel.setStyle("-fx-text-fill: red;");
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle("Erreur");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    // ==================== NAVIGATION CORRIGÉE ====================
    @FXML private void handleHome() { navigateTo("/fxml/MainMenu.fxml"); }
    @FXML private void handleCours() { navigateTo("/fxml/CoursView.fxml"); }
    @FXML private void handleRessources() { navigateTo("/fxml/RessourcesView.fxml"); }
    @FXML private void handleQuestions() { navigateTo("/fxml/QuestionListView.fxml"); }
    @FXML private void handleProjets() { navigateTo("/fxml/PortfolioListView.fxml"); }
    @FXML private void handleEvenements() { showInfo("Événements", "Module en construction"); }
    @FXML private void handleUtilisateurs() { navigateTo("/fxml/User.fxml"); }

    private void navigateTo(String fxmlPath) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();
            Scene scene = titreField.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            showError("Erreur de navigation: " + e.getMessage());
        }
    }

    private void showInfo(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}