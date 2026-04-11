package org.example.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.layout.VBox;
import javafx.stage.Stage;
import java.io.IOException;

public class FrontAccueilController {

    @FXML private VBox coursCard;
    @FXML private VBox ressourcesCard;
    @FXML private VBox questionsCard;
    @FXML private VBox projetsCard;
    @FXML private VBox evenementsCard;
    @FXML private VBox utilisateursCard;

    private Stage getStage() {
        VBox[] candidates = { coursCard, ressourcesCard, questionsCard,
                projetsCard, evenementsCard, utilisateursCard };
        for (VBox v : candidates) {
            if (v != null && v.getScene() != null) {
                return (Stage) v.getScene().getWindow();
            }
        }
        return null;
    }

    private void naviguerVers(String fxmlPath, String titre) {
        try {
            java.net.URL resource = getClass().getResource(fxmlPath);
            if (resource == null) {
                showAlert("Erreur", "Fichier FXML introuvable : " + fxmlPath);
                return;
            }
            FXMLLoader loader = new FXMLLoader(resource);
            Parent root = loader.load();

            Stage stage = getStage();
            if (stage == null) {
                showAlert("Erreur", "Impossible de récupérer la fenêtre principale.");
                return;
            }
            stage.setScene(new Scene(root));
            stage.setTitle(titre);
            stage.setMaximized(true);
            stage.show();

        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur de chargement", e.getMessage());
        }
    }

    @FXML
    private void handleGoToDashboard() {
        naviguerVers("/fxml/MainMenu.fxml", "Path2Learn - Dashboard Administrateur");
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
        naviguerVers("/fxml/QuestionListView.fxml", "Path2Learn - Quiz");
    }

    @FXML
    private void handleProjets() {
        naviguerVers("/fxml/PortfolioListView.fxml", "Path2Learn - Projets");
    }

    @FXML
    private void handleEvenements() {
        showInfoAlert("Événements", "Le module Événements arrive très bientôt !");
    }

    @FXML
    private void handleUtilisateurs() {
        naviguerVers("/fxml/User.fxml", "Path2Learn - Communauté");
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void showInfoAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}