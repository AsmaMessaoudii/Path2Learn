package org.example.controllers;

import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.layout.VBox;
import javafx.stage.Stage;

import java.io.IOException;

public class MainMenuController {

    @FXML
    private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;

    @FXML
    private Label pageTitleLabel;

    @FXML
    private VBox mainContentArea;

    // ==================== NAVIGATION PRINCIPALE ====================

    @FXML
    private void handleHome() {
        resetButtonStyles();
        setActiveButton(homeBtn);
        naviguerVers("/fxml/MainMenu.fxml", "Path2Learn - Tableau de bord");
    }

    @FXML
    private void handleCours() {
        resetButtonStyles();
        setActiveButton(coursBtn);
        naviguerVers("/fxml/CoursView.fxml", "Path2Learn - Gestion des cours");
    }

    @FXML
    private void handleRessources() {
        resetButtonStyles();
        setActiveButton(ressourcesBtn);
        naviguerVers("/fxml/RessourcesView.fxml", "Path2Learn - Gestion des ressources");
    }

    @FXML
    private void handleQuestions() {
        resetButtonStyles();
        setActiveButton(questionsBtn);
        naviguerVers("/fxml/QuestionListView.fxml", "Path2Learn - Questions");
    }

    @FXML
    private void handleProjets() {
        resetButtonStyles();
        setActiveButton(projetsBtn);
        naviguerVers("/fxml/PortfolioListViewBack.fxml", "Path2Learn - Portfolios");
    }

    @FXML
    private void handleEvenements() {
        showAlert("Événements", "Module Événements - Bientôt disponible", Alert.AlertType.INFORMATION);
        resetButtonStyles();
        setActiveButton(evenementsBtn);
    }

    @FXML
    private void handleUtilisateurs() {
        resetButtonStyles();
        setActiveButton(utilisateursBtn);
        naviguerVers("/fxml/User.fxml", "Path2Learn - Utilisateurs");
    }

    // ==================== RETOUR VERS LE SITE FRONT ====================
    @FXML
    private void handleBackToSite() {
        try {
            System.out.println("Retour vers HomePage...");

            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/HomePage.fxml"));
            Parent root = loader.load();

            Stage stage = (Stage) homeBtn.getScene().getWindow();
            Scene scene = new Scene(root);
            stage.setScene(scene);
            stage.setTitle("Path2Learn - Accueil");

            // CRUCIAL : Ne pas mettre en maximisé, utiliser une taille fixe
            stage.setMaximized(false);
            stage.setWidth(1200);
            stage.setHeight(800);
            stage.centerOnScreen();

            // Forcer le refresh de la scène
            stage.sizeToScene();
            stage.show();

        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de revenir à l'accueil: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    // ==================== GESTION DES CARTES (ACCÈS RAPIDES) ====================

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
    }

    @FXML
    private void handleUtilisateursCard() {
        naviguerVers("/fxml/User.fxml", "Path2Learn - Utilisateurs");
    }

    // ==================== MÉTHODE PRINCIPALE DE NAVIGATION ====================

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

    // ==================== GESTION DES STYLES ====================

    private void resetButtonStyles() {
        String defaultStyle = "-fx-background-color: transparent; -fx-text-fill: #A0A0A0; -fx-font-size: 14px; -fx-cursor: hand; -fx-padding: 12 20; -fx-alignment: CENTER_LEFT;";
        homeBtn.setStyle(defaultStyle);
        coursBtn.setStyle(defaultStyle);
        ressourcesBtn.setStyle(defaultStyle);
        questionsBtn.setStyle(defaultStyle);
        projetsBtn.setStyle(defaultStyle);
        evenementsBtn.setStyle(defaultStyle);
        utilisateursBtn.setStyle(defaultStyle);
    }

    private void setActiveButton(Button btn) {
        btn.setStyle("-fx-background-color: #E8F5E9; -fx-text-fill: #81C784; -fx-font-size: 14px; -fx-font-weight: bold; -fx-cursor: hand; -fx-padding: 12 20; -fx-alignment: CENTER_LEFT; -fx-border-color: transparent #81C784 transparent transparent; -fx-border-width: 0 4 0 0;");
    }

    // ==================== ALERTES ====================

    private void showAlert(String title, String message, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    // ==================== INITIALISATION ====================

    @FXML
    private void initialize() {
        setActiveButton(homeBtn);
        pageTitleLabel.setText("Tableau de bord");
    }
}