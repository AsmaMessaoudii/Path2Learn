package org.example.controllers;

import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
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
    }

    @FXML
    private void handleRetour(ActionEvent event) {
        navigateTo("/fxml/QuestionListView.fxml");
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