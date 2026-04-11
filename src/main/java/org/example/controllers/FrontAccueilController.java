package org.example.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.stage.Stage;
import java.io.IOException;

public class FrontAccueilController {

    @FXML
    private void handleGoToDashboard() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/MainMenu.fxml"));
            Parent root = loader.load();

            Stage stage = new Stage();
            Scene scene = new Scene(root);
            stage.setScene(scene);
            stage.setTitle("Path2Learn - Dashboard Administrateur");
            stage.setMaximized(true);
            stage.show();

        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible d'ouvrir le dashboard: " + e.getMessage());
        }
    }

    @FXML
    private void handleCours() {
        showInfoAlert("Cours", "Page des cours - Bientôt disponible");
    }

    @FXML
    private void handleRessources() {
        showInfoAlert("Ressources", "Page des ressources - Bientôt disponible");
    }

    @FXML
    private void handleQuestions() {
        showInfoAlert("Quiz", "Page des quiz - Bientôt disponible");
    }

    @FXML
    private void handleProjets() {
        showInfoAlert("Projets", "Page des projets - Bientôt disponible");
    }

    @FXML
    private void handleEvenements() {
        showInfoAlert("Événements", "Page des événements - Bientôt disponible");
    }

    @FXML
    private void handleUtilisateurs() {
        showInfoAlert("Communauté", "Page de la communauté - Bientôt disponible");
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