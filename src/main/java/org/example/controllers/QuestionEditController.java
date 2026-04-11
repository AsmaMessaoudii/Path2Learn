package org.example.controllers;

import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.stage.Stage;
import org.example.Models.Question;
import org.example.Services.QuestionService;
import java.io.IOException;
import java.sql.Date;
import java.sql.SQLException;
import java.time.LocalDate;

public class QuestionEditController {

    @FXML private TextField titreField;
    @FXML private TextArea descriptionArea;
    @FXML private TextField dureeField;
    @FXML private TextField noteMaxField;
    @FXML private TextField userIdField;
    @FXML private DatePicker datePicker;
    @FXML private Label statusLabel;
    @FXML private Label questionIdLabel;
    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;

    private QuestionService questionService;
    private Question currentQuestion;

    public void setQuestion(Question question) {
        this.currentQuestion = question;
        loadQuestionData();
    }

    @FXML
    public void initialize() {
        questionService = new QuestionService();
    }

    private void loadQuestionData() {
        if (currentQuestion != null) {
            questionIdLabel.setText("Modification de la question #" + currentQuestion.getId());
            titreField.setText(currentQuestion.getTitre());
            descriptionArea.setText(currentQuestion.getDescription());
            dureeField.setText(String.valueOf(currentQuestion.getDuree()));
            noteMaxField.setText(String.valueOf(currentQuestion.getNoteMax()));
            userIdField.setText(String.valueOf(currentQuestion.getUserId()));

            if (currentQuestion.getDateCreation() != null) {
                datePicker.setValue(currentQuestion.getDateCreation().toLocalDate());
            } else {
                datePicker.setValue(LocalDate.now());
            }
        }
    }

    @FXML
    private void handleModifier(ActionEvent event) {
        if (!validateFields()) {
            return;
        }

        try {
            currentQuestion.setTitre(titreField.getText());
            currentQuestion.setDescription(descriptionArea.getText());
            currentQuestion.setDateCreation(Date.valueOf(datePicker.getValue()));
            currentQuestion.setDuree(Integer.parseInt(dureeField.getText()));
            currentQuestion.setNoteMax(Float.parseFloat(noteMaxField.getText()));
            currentQuestion.setUserId(Integer.parseInt(userIdField.getText()));

            questionService.modifier(currentQuestion);

            Alert alert = new Alert(Alert.AlertType.INFORMATION);
            alert.setTitle("Succès");
            alert.setHeaderText(null);
            alert.setContentText("Question modifiée avec succès !");
            alert.showAndWait();

            handleRetour(event);

        } catch (SQLException | NumberFormatException e) {
            e.printStackTrace();
            showError("Erreur: " + e.getMessage());
        }
    }

    @FXML
    private void handleAnnuler(ActionEvent event) {
        loadQuestionData();
        statusLabel.setText("Modifications annulées");
        statusLabel.setStyle("-fx-text-fill: blue;");
    }

    @FXML
    private void handleRetour(ActionEvent event) {
        naviguerVers("/fxml/QuestionListView.fxml", "Path2Learn - Questions");
    }

    private boolean validateFields() {
        if (titreField.getText().trim().isEmpty()) {
            showError("Le titre est obligatoire");
            return false;
        }
        if (descriptionArea.getText().trim().isEmpty()) {
            showError("La description est obligatoire");
            return false;
        }
        if (dureeField.getText().trim().isEmpty()) {
            showError("La durée est obligatoire");
            return false;
        }
        if (noteMaxField.getText().trim().isEmpty()) {
            showError("La note maximale est obligatoire");
            return false;
        }
        if (userIdField.getText().trim().isEmpty()) {
            showError("L'ID utilisateur est obligatoire");
            return false;
        }

        try {
            Integer.parseInt(dureeField.getText());
            Float.parseFloat(noteMaxField.getText());
            Integer.parseInt(userIdField.getText());
        } catch (NumberFormatException e) {
            showError("Veuillez entrer des nombres valides");
            return false;
        }

        return true;
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

    // ==================== MÉTHODES DE NAVIGATION CORRIGÉES ====================

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
        naviguerVers("/fxml/User.fxml", "Path2Learn - Utilisateurs");
    }

    // ==================== MÉTHODES UTILITAIRES DE NAVIGATION ====================

    private void naviguerVers(String fxmlPath, String titre) {
        try {
            java.net.URL resource = getClass().getResource(fxmlPath);
            if (resource == null) {
                showError("Page non trouvée: " + fxmlPath);
                return;
            }
            FXMLLoader loader = new FXMLLoader(resource);
            Parent root = loader.load();
            Stage stage = (Stage) titreField.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle(titre);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            showError("Erreur de navigation: " + e.getMessage());
        }
    }

    private void showInfoAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}