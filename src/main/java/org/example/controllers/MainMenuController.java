package org.example.controllers;

import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.Button;
import javafx.scene.layout.VBox;
import javafx.stage.Stage;

import java.io.IOException;

public class MainMenuController {

    @FXML
    private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;

    @FXML
    private VBox coursCard, ressourcesCard, questionsCard, projetsCard, evenementsCard, utilisateursCard;

    @FXML
    private void handleHome() {
        resetButtonStyles();
        setActiveButton(homeBtn);
    }

    @FXML
    private void handleCours() {
        naviguerVers("/fxml/CoursView.fxml", "Path2Learn - Gestion des cours");
    }

    @FXML
    private void handleRessources() {
        naviguerVers("/fxml/RessourcesView.fxml", "Path2Learn - Gestion des ressources");
    }

    @FXML
    private void handleQuestions() {
        naviguerVers("/fxml/QuestionListView.fxml", "Path2Learn - Questions");
    }

    @FXML
    private void handleProjets(ActionEvent actionEvent) {
        naviguerVers("/fxml/PortfolioListView.fxml", "Path2Learn - Portfolios");
    }

    @FXML
    private void handleEvenements() {
        showAlert("Événements", "Module Événements - Bientôt disponible", Alert.AlertType.INFORMATION);
        resetButtonStyles();
        setActiveButton(evenementsBtn);
    }

    @FXML
    private void handleUtilisateurs() {
        naviguerVers("/fxml/User.fxml", "Path2Learn - Utilisateurs");
    }

    // Navigation vers les cartes
    @FXML
    private void handleCoursCard() {
        naviguerVers("/fxml/CoursView.fxml", "Path2Learn - Gestion des cours");
    }

    @FXML
    private void handleRessourcesCard() {
        naviguerVers("/fxml/RessourcesView.fxml", "Path2Learn - Gestion des ressources");
    }

    @FXML
    private void handleQuestionsCard() {
        naviguerVers("/fxml/QuestionListView.fxml", "Path2Learn - Questions");
    }

    @FXML
    private void handleProjetsCard() {
        naviguerVers("/fxml/PortfolioListView.fxml", "Path2Learn - Portfolios");
    }

    @FXML
    private void handleEvenementsCard() {
        showAlert("Événements", "Module Événements - Bientôt disponible", Alert.AlertType.INFORMATION);
        resetButtonStyles();
        setActiveButton(evenementsBtn);
    }

    @FXML
    private void handleUtilisateursCard() {
        naviguerVers("/fxml/User.fxml", "Path2Learn - Utilisateurs");
    }

    private void naviguerVers(String fxmlPath, String titre) {
        try {
            java.net.URL resource = getClass().getResource(fxmlPath);
            if (resource == null) {
                showAlert("Erreur", "Page non trouvée: " + fxmlPath, Alert.AlertType.ERROR);
                return;
            }

            FXMLLoader loader = new FXMLLoader(resource);
            Parent root = loader.load();
            Stage stage = (Stage) homeBtn.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle(titre);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger la page: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void resetButtonStyles() {
        String defaultStyle = "-fx-background-color: transparent; -fx-text-fill: #555555; -fx-font-size: 13px; -fx-cursor: hand; -fx-padding: 0 0 5 0;";
        homeBtn.setStyle(defaultStyle);
        coursBtn.setStyle(defaultStyle);
        ressourcesBtn.setStyle(defaultStyle);
        questionsBtn.setStyle(defaultStyle);
        projetsBtn.setStyle(defaultStyle);
        evenementsBtn.setStyle(defaultStyle);
        utilisateursBtn.setStyle(defaultStyle);
    }

    private void setActiveButton(Button btn) {
        btn.setStyle("-fx-background-color: transparent; -fx-text-fill: #81C784; -fx-font-size: 13px; -fx-font-weight: bold; -fx-cursor: hand; -fx-padding: 0 0 5 0; -fx-border-color: transparent transparent #81C784 transparent; -fx-border-width: 0 0 2 0;");
    }

    private void showAlert(String title, String message, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    @FXML
    private void initialize() {
        addCardHoverEffect(coursCard);
        addCardHoverEffect(ressourcesCard);
        addCardHoverEffect(questionsCard);
        addCardHoverEffect(projetsCard);
        addCardHoverEffect(evenementsCard);
        addCardHoverEffect(utilisateursCard);

        setActiveButton(homeBtn);
    }

    private void addCardHoverEffect(VBox card) {
        if (card != null) {
            card.setOnMouseEntered(e -> {
                card.setStyle("-fx-scale-x: 1.02; -fx-scale-y: 1.02; -fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.12), 10, 0, 0, 2);");
            });
            card.setOnMouseExited(e -> {
                card.setStyle("-fx-scale-x: 1; -fx-scale-y: 1; -fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.05), 8, 0, 0, 2);");
            });
        }
    }
}