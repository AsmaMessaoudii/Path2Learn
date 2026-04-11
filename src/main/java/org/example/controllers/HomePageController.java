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

        // Forcer l'affichage du bouton admin
        if (adminBtn != null) {
            adminBtn.setVisible(true);
            adminBtn.setManaged(true);
            adminBtn.setStyle("-fx-background-color: #FF9800; -fx-text-fill: white; -fx-font-size: 13px; -fx-font-weight: bold; -fx-cursor: hand; -fx-padding: 5 15; -fx-background-radius: 20;");
        }

        // Appliquer un refresh de la scène
        refreshScene();
    }

    private void refreshScene() {
        // Forcer le recalcul de la mise en page
        if (adminBtn != null && adminBtn.getScene() != null) {
            adminBtn.getScene().getWindow().sizeToScene();
        }
    }

    @FXML
    private void handleHome() {
        System.out.println("Page d'accueil");
    }

    @FXML
    private void handleCours() {
        showInfo("Cours", "Page des cours - À venir");
    }

    @FXML
    private void handleRessources() {
        showInfo("Ressources", "Page des ressources - À venir");
    }

    @FXML
    private void handleQuestions() {
        showInfo("Quiz", "Page des quiz - À venir");
    }

    @FXML
    private void handleProjets() {
        showInfo("Projets", "Page des projets - À venir");
    }

    @FXML
    private void handleEvenements() {
        showInfo("Événements", "Page des événements - À venir");
    }

    @FXML
    private void handleUtilisateurs() {
        showInfo("Communauté", "Page de la communauté - À venir");
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
        try {
            System.out.println("Navigation vers MainMenu...");

            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/MainMenu.fxml"));
            Parent root = loader.load();

            Stage stage = (Stage) adminBtn.getScene().getWindow();
            Scene scene = new Scene(root);
            stage.setScene(scene);
            stage.setTitle("Path2Learn - Administration");
            stage.setMaximized(true);
            stage.show();

        } catch (IOException e) {
            e.printStackTrace();
            showError("Erreur", "Impossible d'ouvrir le panneau d'administration: " + e.getMessage());
        }
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