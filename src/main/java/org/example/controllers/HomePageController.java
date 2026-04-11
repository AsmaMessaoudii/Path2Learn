package org.example.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.Button;
import javafx.scene.layout.VBox;
import javafx.stage.Stage;

import java.io.IOException;

public class HomePageController {

    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;
    @FXML private Button loginBtn, registerBtn, adminBtn;
    @FXML private VBox adminSection;
    @FXML private VBox coursCard, ressourcesCard, questionsCard, projetsCard, evenementsCard, utilisateursCard;

    @FXML
    public void initialize() {
        System.out.println("HomePageController initialisé");

        if (adminBtn != null) {
            adminBtn.setVisible(true);
            adminBtn.setManaged(true);
            adminBtn.setStyle("-fx-background-color: #FF9800; -fx-text-fill: white; -fx-font-size: 13px; -fx-font-weight: bold; -fx-cursor: hand; -fx-padding: 5 15; -fx-background-radius: 20;");
        }

        refreshScene();
    }

    private void refreshScene() {
        if (adminBtn != null && adminBtn.getScene() != null) {
            adminBtn.getScene().getWindow().sizeToScene();
        }
    }

    private void naviguerVers(String fxmlPath, String titre) {
        try {
            java.net.URL resource = getClass().getResource(fxmlPath);
            if (resource == null) {
                showError("Erreur", "Fichier FXML introuvable : " + fxmlPath);
                return;
            }
            FXMLLoader loader = new FXMLLoader(resource);
            Parent root = loader.load();

            Stage stage = null;
            if (homeBtn != null && homeBtn.getScene() != null) {
                stage = (Stage) homeBtn.getScene().getWindow();
            } else if (coursCard != null && coursCard.getScene() != null) {
                stage = (Stage) coursCard.getScene().getWindow();
            }

            if (stage == null) {
                showError("Erreur", "Impossible de récupérer la fenêtre principale.");
                return;
            }
            
            stage.setScene(new Scene(root));
            stage.setTitle(titre);
            stage.setMaximized(true);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            showError("Erreur de chargement", e.getMessage());
        }
    }

    @FXML
    private void handleHome() {
        System.out.println("Page d'accueil");
    }

    @FXML
    private void handleCours() {
        naviguerVers("/fxml/CoursListFrontView.fxml", "Path2Learn - Cours");
    }

    @FXML
    private void handleRessources() {
        naviguerVers("/fxml/RessourcesViewFront.fxml", "Path2Learn - Ressources");
    }

    @FXML
    private void handleQuestions() {
        naviguerVers("/fxml/QuizList.fxml", "Path2Learn - Quiz");
    }

    @FXML
    private void handleProjets() {
        naviguerVers("/fxml/PortfolioListView.fxml", "Path2Learn - Projets");
    }

    @FXML
    private void handleEvenements() {
        showInfo("Événements", "Page des événements - À venir");
    }

    @FXML
    private void handleUtilisateurs() {
        naviguerVers("/fxml/User.fxml", "Path2Learn - Communauté");
    }

    @FXML
    private void handleLogin() {
        showInfo("Connexion", "Formulaire de connexion - À venir");
    }

    @FXML
    private void handleRegister() {
        showInfo("Inscription", "Formulaire d'inscription - À venir");
    }

    @FXML
    private void handleGoToAdmin() {
        naviguerVers("/fxml/MainMenu.fxml", "Path2Learn - Administration");
    }

    private void showInfo(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void showError(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}