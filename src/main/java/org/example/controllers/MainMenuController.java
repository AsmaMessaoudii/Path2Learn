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

public class MainMenuController {

    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;
    @FXML private VBox coursCard, ressourcesCard, questionsCard, projetsCard, evenementsCard, utilisateursCard;

    @FXML
    private void handleHome() {
        // Recharger la page d'accueil
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/MainMenu.fxml"));
            Parent root = loader.load();
            Stage stage = (Stage) homeBtn.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle("Path2Learn - Accueil");
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de revenir à l'accueil");
        }
    }

    @FXML
    private void handleCours() {
        try {
            // Charger la page Cours
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/CoursView.fxml"));
            Parent root = loader.load();

            // Remplacer toute la scène
            Stage stage = (Stage) coursBtn.getScene().getWindow();
            Scene scene = new Scene(root);
            stage.setScene(scene);
            stage.setTitle("Path2Learn - Gestion des cours");
            stage.show();

        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger la page des cours: " + e.getMessage());
        }
    }

    @FXML
    private void handleRessources() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/RessourcesView.fxml"));
            Parent root = loader.load();
            Stage stage = (Stage) ressourcesBtn.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle("Path2Learn - Gestion des ressources");
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger la page des ressources: " + e.getMessage());
        }
    }

    @FXML
    private void handleQuestions() {
        showAlert("Quiz", "Module Quiz - Bientôt disponible");
    }

    @FXML
    private void handleProjets() {
        showAlert("Projets", "Module Projets - Bientôt disponible");
    }

    @FXML
    private void handleEvenements() {
        showAlert("Événements", "Module Événements - Bientôt disponible");
    }

    @FXML
    private void handleUtilisateurs() {
        showAlert("Utilisateurs", "Module Utilisateurs - Bientôt disponible");
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    @FXML
    private void initialize() {
        // Animation des cartes
        if (coursCard != null) {
            coursCard.setOnMouseEntered(e -> coursCard.setStyle(coursCard.getStyle() + "-fx-scale-x: 1.02; -fx-scale-y: 1.02;"));
            coursCard.setOnMouseExited(e -> coursCard.setStyle(coursCard.getStyle().replace("-fx-scale-x: 1.02; -fx-scale-y: 1.02;", "")));
        }
        if (ressourcesCard != null) {
            ressourcesCard.setOnMouseEntered(e -> ressourcesCard.setStyle(ressourcesCard.getStyle() + "-fx-scale-x: 1.02; -fx-scale-y: 1.02;"));
            ressourcesCard.setOnMouseExited(e -> ressourcesCard.setStyle(ressourcesCard.getStyle().replace("-fx-scale-x: 1.02; -fx-scale-y: 1.02;", "")));
        }
        if (questionsCard != null) {
            questionsCard.setOnMouseEntered(e -> questionsCard.setStyle(questionsCard.getStyle() + "-fx-scale-x: 1.02; -fx-scale-y: 1.02;"));
            questionsCard.setOnMouseExited(e -> questionsCard.setStyle(questionsCard.getStyle().replace("-fx-scale-x: 1.02; -fx-scale-y: 1.02;", "")));
        }
        if (projetsCard != null) {
            projetsCard.setOnMouseEntered(e -> projetsCard.setStyle(projetsCard.getStyle() + "-fx-scale-x: 1.02; -fx-scale-y: 1.02;"));
            projetsCard.setOnMouseExited(e -> projetsCard.setStyle(projetsCard.getStyle().replace("-fx-scale-x: 1.02; -fx-scale-y: 1.02;", "")));
        }
        if (evenementsCard != null) {
            evenementsCard.setOnMouseEntered(e -> evenementsCard.setStyle(evenementsCard.getStyle() + "-fx-scale-x: 1.02; -fx-scale-y: 1.02;"));
            evenementsCard.setOnMouseExited(e -> evenementsCard.setStyle(evenementsCard.getStyle().replace("-fx-scale-x: 1.02; -fx-scale-y: 1.02;", "")));
        }
        if (utilisateursCard != null) {
            utilisateursCard.setOnMouseEntered(e -> utilisateursCard.setStyle(utilisateursCard.getStyle() + "-fx-scale-x: 1.02; -fx-scale-y: 1.02;"));
            utilisateursCard.setOnMouseExited(e -> utilisateursCard.setStyle(utilisateursCard.getStyle().replace("-fx-scale-x: 1.02; -fx-scale-y: 1.02;", "")));
        }
    }
}