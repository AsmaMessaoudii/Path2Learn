package org.example.controllers;

import javafx.collections.FXCollections;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import org.example.Models.Choix;
import org.example.Models.Question;
import org.example.Services.ChoixService;
import org.example.Services.QuestionService;
import java.io.IOException;
import java.sql.SQLException;
import java.util.List;

public class ChoixEditController {

    @FXML private TextArea contenuArea;
    @FXML private RadioButton correctOui;
    @FXML private RadioButton correctNon;
    @FXML private ComboBox<String> questionCombo;
    @FXML private Label choixIdLabel;
    @FXML private Label statusLabel;
    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;

    private ChoixService choixService;
    private QuestionService questionService;
    private Choix currentChoix;
    private List<Question> questions;

    public void setChoix(Choix choix) {
        this.currentChoix = choix;
        loadChoixData();
    }

    @FXML
    public void initialize() {
        choixService = new ChoixService();
        questionService = new QuestionService();
        loadQuestions();
    }

    private void loadQuestions() {
        try {
            questions = questionService.recuperer();
            for (Question q : questions) {
                questionCombo.getItems().add(q.getId() + " - " + q.getTitre());
            }
        } catch (SQLException e) {
            showError("Erreur: " + e.getMessage());
        }
    }

    private void loadChoixData() {
        if (currentChoix != null) {
            choixIdLabel.setText("Modification de l'option #" + currentChoix.getId());
            contenuArea.setText(currentChoix.getContenu());

            if (currentChoix.isEstCorrect()) {
                correctOui.setSelected(true);
            } else {
                correctNon.setSelected(true);
            }

            // Sélectionner la question dans le combo
            for (Question q : questions) {
                if (q.getId() == currentChoix.getQuestionId()) {
                    questionCombo.getSelectionModel().select(q.getId() + " - " + q.getTitre());
                    break;
                }
            }
        }
    }

    @FXML
    private void handleModifier(ActionEvent event) {
        if (!validateFields()) {
            return;
        }

        try {
            currentChoix.setContenu(contenuArea.getText());
            currentChoix.setEstCorrect(correctOui.isSelected());

            String selected = questionCombo.getSelectionModel().getSelectedItem();
            int questionId = Integer.parseInt(selected.split(" - ")[0]);
            currentChoix.setQuestionId(questionId);

            choixService.modifier(currentChoix);

            Alert alert = new Alert(Alert.AlertType.INFORMATION);
            alert.setTitle("Succès");
            alert.setHeaderText(null);
            alert.setContentText("Option modifiée avec succès !");
            alert.showAndWait();

            handleRetour(event);

        } catch (SQLException | NumberFormatException e) {
            showError("Erreur: " + e.getMessage());
        }
    }

    @FXML
    private void handleAnnuler(ActionEvent event) {
        loadChoixData();
        statusLabel.setText("Modifications annulées");
    }

    @FXML
    private void handleRetour(ActionEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/ChoixListView.fxml"));
            Parent root = loader.load();

            ChoixListController controller = loader.getController();
            Question q = new Question();
            q.setId(currentChoix.getQuestionId());
            q.setTitre("Question #" + currentChoix.getQuestionId());
            controller.setQuestion(q);

            Scene scene = contenuArea.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            showError("Erreur lors du retour");
        }
    }

    private boolean validateFields() {
        if (contenuArea.getText().trim().isEmpty()) {
            showError("Le contenu de l'option est obligatoire");
            return false;
        }

        if (questionCombo.getSelectionModel().getSelectedItem() == null) {
            showError("Veuillez sélectionner une question");
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

    // Navigation Menu
    @FXML private void handleHome() { navigateTo("/fxml/MainMenu.fxml"); }
    @FXML private void handleCours() { showAlert("Cours", "Page en construction"); }
    @FXML private void handleRessources() { showAlert("Ressources", "Page en construction"); }
    @FXML private void handleQuestions() { navigateTo("/fxml/QuestionListView.fxml"); }
    @FXML private void handleProjets() { showAlert("Projets", "Page en construction"); }
    @FXML private void handleEvenements() { showAlert("Événements", "Page en construction"); }
    @FXML private void handleUtilisateurs() { showAlert("Communauté", "Page en construction"); }

    private void navigateTo(String fxmlPath) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();
            Scene scene = contenuArea.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            showError("Erreur de navigation: " + e.getMessage());
        }
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}