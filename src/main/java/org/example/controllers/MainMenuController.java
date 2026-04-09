package org.example.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.control.Alert;
import javafx.scene.control.Button;
import javafx.scene.input.MouseEvent;
import javafx.scene.layout.VBox;

import java.io.IOException;

public class MainMenuController {

    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;
    @FXML private VBox coursCard, ressourcesCard, questionsCard, projetsCard, evenementsCard, utilisateursCard;

    @FXML
    private void handleHome() {
        showAlert("Accueil", "Bienvenue sur Path2Learn");
        resetButtonStyles();
        setActiveButton(homeBtn);
    }

    @FXML
    private void handleCours() {
        showAlert("Cours", "Découvrez tous nos cours disponibles");
        resetButtonStyles();
        setActiveButton(coursBtn);
    }

    @FXML
    private void handleRessources() {
        showAlert("Ressources", "Accédez à toutes les ressources pédagogiques");
        resetButtonStyles();
        setActiveButton(ressourcesBtn);
    }

    @FXML
    private void handleQuestions() {
        showAlert("Quiz", "Testez vos connaissances avec nos quiz");
        resetButtonStyles();
        setActiveButton(questionsBtn);
    }

    @FXML
    private void handleProjets() {
        showAlert("Projets", "Découvrez les projets réalisés par nos apprenants");
        resetButtonStyles();
        setActiveButton(projetsBtn);
    }

    @FXML
    private void handleEvenements() {
        showAlert("Événements", "Participez à nos événements exclusifs");
        resetButtonStyles();
        setActiveButton(evenementsBtn);
    }

    @FXML
    private void handleUtilisateurs() {
        resetButtonStyles();
        setActiveButton(utilisateursBtn);

        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/User.fxml"));
            VBox usersPage = loader.load(); // or AnchorPane if root is AnchorPane

            VBox root = (VBox) homeBtn.getScene().getRoot();
            if (root.getChildren().size() > 1) {
                root.getChildren().set(1, usersPage);
            } else {
                root.getChildren().add(usersPage);
            }
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger la page Utilisateurs");
        }

    }

    private void resetButtonStyles() {
        String defaultStyle = "-fx-background-color: transparent; -fx-text-fill: #555555; -fx-font-size: 14px; -fx-cursor: hand; -fx-padding: 0 0 5 0;";
        homeBtn.setStyle(defaultStyle);
        coursBtn.setStyle(defaultStyle);
        ressourcesBtn.setStyle(defaultStyle);
        questionsBtn.setStyle(defaultStyle);
        projetsBtn.setStyle(defaultStyle);
        evenementsBtn.setStyle(defaultStyle);
        utilisateursBtn.setStyle(defaultStyle);
    }

    private void setActiveButton(Button btn) {
        btn.setStyle("-fx-background-color: transparent; -fx-text-fill: #81C784; -fx-font-size: 14px; -fx-font-weight: bold; -fx-cursor: hand; -fx-padding: 0 0 5 0; -fx-border-color: transparent transparent #81C784 transparent; -fx-border-width: 0 0 2 0;");
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
        addCardHoverEffect(coursCard);
        addCardHoverEffect(ressourcesCard);
        addCardHoverEffect(questionsCard);
        addCardHoverEffect(projetsCard);
        addCardHoverEffect(evenementsCard);
        addCardHoverEffect(utilisateursCard);
    }

    private void addCardHoverEffect(VBox card) {
        if (card != null) {
            card.setOnMouseEntered(e -> {
                card.setStyle(card.getStyle() + "-fx-scale-x: 1.02; -fx-scale-y: 1.02;");
                card.setStyle(card.getStyle().replace("rgba(0,0,0,0.05)", "rgba(0,0,0,0.12)"));
            });
            card.setOnMouseExited(e -> {
                String style = card.getStyle();
                style = style.replace("-fx-scale-x: 1.02; -fx-scale-y: 1.02;", "");
                style = style.replace("rgba(0,0,0,0.12)", "rgba(0,0,0,0.05)");
                card.setStyle(style);
            });
        }
    }
}