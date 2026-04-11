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

    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn,
            projetsBtn, evenementsBtn, utilisateursBtn;
    @FXML private Button loginBtn, registerBtn, adminBtn;
    @FXML private VBox   adminSection;
    @FXML private VBox   coursCard, ressourcesCard, questionsCard,
            projetsCard, evenementsCard, utilisateursCard;

    @FXML
    public void initialize() {
        System.out.println("HomePageController initialisé");
        if (adminBtn != null) {
            adminBtn.setVisible(true);
            adminBtn.setManaged(true);
            adminBtn.setStyle(
                    "-fx-background-color: #FF9800; -fx-text-fill: white;" +
                            "-fx-font-size: 13px; -fx-font-weight: bold; -fx-cursor: hand;" +
                            "-fx-padding: 5 15; -fx-background-radius: 20;");
        }
    }

    @FXML private void handleHome()         { /* already here */ }
    @FXML private void handleCours()        { info("Cours",       "Page des cours — À venir"); }
    @FXML private void handleRessources()   { info("Ressources",  "Page des ressources — À venir"); }
    @FXML private void handleProjets()      { info("Projets",     "Page des projets — À venir"); }
    @FXML private void handleEvenements()   { info("Événements",  "Page des événements — À venir"); }
    @FXML private void handleUtilisateurs() { info("Communauté",  "Page communauté — À venir"); }
    @FXML private void handleLogin()        { info("Connexion",   "Formulaire de connexion — À venir"); }
    @FXML private void handleRegister()     { info("Inscription", "Formulaire d'inscription — À venir"); }

    /** Navigate to the Quiz list. */
    @FXML
    private void handleQuestions() {
        navigateTo("/fxml/QuizList.fxml", "Path2Learn — Quiz", questionsBtn);
    }

    @FXML
    private void handleGoToAdmin() {
        navigateTo("/fxml/MainMenu.fxml", "Path2Learn — Administration", adminBtn);
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private void navigateTo(String fxml, String title, javafx.scene.Node src) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxml));
            Parent root = loader.load();
            Stage stage = (Stage) src.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle(title);
            stage.setMaximized(true);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            error("Erreur", "Impossible d'ouvrir la page : " + e.getMessage());
        }
    }

    private void info(String title, String msg) {
        Alert a = new Alert(Alert.AlertType.INFORMATION);
        a.setTitle(title); a.setHeaderText(null); a.setContentText(msg);
        a.showAndWait();
    }

    private void error(String title, String msg) {
        Alert a = new Alert(Alert.AlertType.ERROR);
        a.setTitle(title); a.setHeaderText(null); a.setContentText(msg);
        a.showAndWait();
    }
}