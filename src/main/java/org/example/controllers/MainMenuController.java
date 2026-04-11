package org.example.controllers;

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

    // ── Sidebar ──────────────────────────────────────────────────────────────
    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn,
            projetsBtn, evenementsBtn, utilisateursBtn;

    // ── Cartes raccourcis ────────────────────────────────────────────────────
    @FXML private VBox coursCard, ressourcesCard, questionsCard,
            projetsCard, evenementsCard, utilisateursCard;

    // ── Labels de statistiques (optionnels — peuvent rester à "—") ───────────
    @FXML private Label statsCoursLabel, statsRessourcesLabel,
            statsQuizLabel, statsProjetsLabel;

    // ────────────────────────────────────────────────────────────────────────

    @FXML
    private void initialize() {
        setActiveButton(homeBtn);
        addCardHoverEffect(coursCard);
        addCardHoverEffect(ressourcesCard);
        addCardHoverEffect(questionsCard);
        addCardHoverEffect(projetsCard);
        addCardHoverEffect(evenementsCard);
        addCardHoverEffect(utilisateursCard);

        // Charger les statistiques réelles ici si nécessaire
        // statsCoursLabel.setText(String.valueOf(coursService.count()));
    }

    // ── Navigation sidebar ───────────────────────────────────────────────────

    @FXML
    private void handleHome() {
        setActiveButton(homeBtn);
    }

    @FXML
    private void handleCours() {
        naviguerVers("/fxml/CoursView.fxml", "Path2Learn - Cours");
    }

    @FXML
    private void handleRessources() {
        naviguerVers("/fxml/RessourcesView.fxml", "Path2Learn - Ressources");
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
        showAlert("Événements", "Module Événements — Bientôt disponible.", Alert.AlertType.INFORMATION);
    }

    @FXML
    private void handleUtilisateurs() {
        naviguerVers("/fxml/User.fxml", "Path2Learn - Communauté");
    }

    // ── Utilitaires ──────────────────────────────────────────────────────────

    private void naviguerVers(String fxmlPath, String titre) {
        try {
            java.net.URL resource = getClass().getResource(fxmlPath);
            if (resource == null) {
                showAlert("Erreur", "Page non trouvée : " + fxmlPath, Alert.AlertType.ERROR);
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
            showAlert("Erreur", "Impossible de charger la page : " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void setActiveButton(Button active) {
        String defaultStyle =
                "-fx-background-color: transparent; -fx-text-fill: #555555; -fx-font-size: 13px; "
                        + "-fx-alignment: CENTER_LEFT; -fx-cursor: hand; -fx-padding: 10 16; "
                        + "-fx-background-radius: 0; -fx-max-width: Infinity;";
        String activeStyle =
                "-fx-background-color: #E8F5E9; -fx-text-fill: #2E7D32; -fx-font-size: 13px; "
                        + "-fx-font-weight: bold; -fx-alignment: CENTER_LEFT; -fx-cursor: hand; "
                        + "-fx-padding: 10 16; -fx-background-radius: 0; "
                        + "-fx-border-color: transparent #4CAF50 transparent transparent; "
                        + "-fx-border-width: 0 3 0 0; -fx-max-width: Infinity;";

        for (Button btn : new Button[]{homeBtn, coursBtn, ressourcesBtn, questionsBtn,
                projetsBtn, evenementsBtn, utilisateursBtn}) {
            if (btn != null) btn.setStyle(defaultStyle);
        }
        if (active != null) active.setStyle(activeStyle);
    }

    private void showAlert(String title, String message, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void addCardHoverEffect(VBox card) {
        if (card == null) return;
        String base = card.getStyle();
        card.setOnMouseEntered(e ->
                card.setStyle(base + "-fx-border-color: #A5D6A7; -fx-scale-x: 1.01; -fx-scale-y: 1.01;")
        );
        card.setOnMouseExited(e -> card.setStyle(base));
    }
}
