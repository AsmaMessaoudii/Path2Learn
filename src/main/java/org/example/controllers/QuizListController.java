package org.example.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.Separator;
import javafx.scene.layout.*;
import javafx.stage.Stage;

import org.example.Models.Choix;
import org.example.Models.Question;
import org.example.Services.ChoixService;
import org.example.Services.QuestionService;

import java.io.IOException;
import java.sql.SQLException;
import java.util.List;

public class QuizListController {

    @FXML private FlowPane quizCardsPane;
    @FXML private VBox emptyState;
    @FXML private Label totalLabel;

    // Navigation buttons
    @FXML private Button homeBtn;
    @FXML private Button coursBtn;
    @FXML private Button ressourcesBtn;
    @FXML private Button questionsBtn;
    @FXML private Button projetsBtn;
    @FXML private Button evenementsBtn;
    @FXML private Button utilisateursBtn;
    @FXML private Button loginBtn;
    @FXML private Button registerBtn;
    @FXML private Button adminBtn;

    private final QuestionService questionService = new QuestionService();
    private final ChoixService choixService = new ChoixService();

    @FXML
    public void initialize() {
        loadCards();
    }

    private void loadCards() {
        try {
            List<Question> questions = questionService.recuperer();
            List<Choix> allChoix = choixService.recuperer();

            for (Question q : questions) {
                List<Choix> mine = allChoix.stream()
                        .filter(c -> c.getQuestionId() == q.getId())
                        .toList();
                q.setChoix(mine);
            }

            if (questions.isEmpty()) {
                showEmpty();
                totalLabel.setText("0 question disponible");
                return;
            }

            totalLabel.setText(questions.size() + " question(s) disponible(s)");
            quizCardsPane.getChildren().clear();
            for (Question q : questions) {
                quizCardsPane.getChildren().add(buildCard(q));
            }

        } catch (SQLException e) {
            e.printStackTrace();
            showError("Erreur DB", "Impossible de charger les quiz : " + e.getMessage());
        }
    }

    private void showEmpty() {
        quizCardsPane.setVisible(false);
        quizCardsPane.setManaged(false);
        emptyState.setVisible(true);
        emptyState.setManaged(true);
    }

    private VBox buildCard(Question question) {
        VBox card = new VBox(12);
        card.setPrefWidth(330);
        card.setPadding(new Insets(20, 20, 16, 20));
        card.setStyle(
                "-fx-background-color: white;" +
                        "-fx-background-radius: 16;" +
                        "-fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.10), 12, 0, 0, 3);"
        );

        HBox topRow = new HBox(12);
        topRow.setAlignment(Pos.CENTER_LEFT);

        Label icon = new Label("❓");
        icon.setStyle("-fx-font-size: 26px;");

        VBox titleBox = new VBox(4);
        Label titleLbl = new Label(question.getTitre());
        titleLbl.setWrapText(true);
        titleLbl.setMaxWidth(240);
        titleLbl.setStyle("-fx-font-size: 15px; -fx-font-weight: bold; -fx-text-fill: #2E5C2E;");

        String descText = (question.getDescription() != null && !question.getDescription().isBlank())
                ? question.getDescription() : "Aucune description";
        Label descLbl = new Label(descText);
        descLbl.setWrapText(true);
        descLbl.setMaxWidth(240);
        descLbl.setStyle("-fx-font-size: 12px; -fx-text-fill: #888;");

        titleBox.getChildren().addAll(titleLbl, descLbl);
        topRow.getChildren().addAll(icon, titleBox);

        Separator sep = new Separator();

        HBox chips = new HBox(8);
        chips.setAlignment(Pos.CENTER_LEFT);

        String dureeText = (question.getDuree() > 0) ? "⏱ " + question.getDuree() + " min" : "⏱ Illimité";
        chips.getChildren().add(chip(dureeText, "#E3F2FD", "#1565C0"));

        String scoreText = "⭐ " + (int) question.getNoteMax() + " pts";
        chips.getChildren().add(chip(scoreText, "#FFF8E1", "#E65100"));

        int nbChoix = (question.getChoix() != null) ? question.getChoix().size() : 0;
        chips.getChildren().add(chip("📋 " + nbChoix + " choix", "#E8F5E9", "#2E7D32"));

        Region spacer = new Region();
        VBox.setVgrow(spacer, Priority.ALWAYS);

        Button startBtn = new Button("▶  Commencer");
        startBtn.setMaxWidth(Double.MAX_VALUE);
        applyBtnStyle(startBtn, false);
        startBtn.setOnMouseEntered(e -> applyBtnStyle(startBtn, true));
        startBtn.setOnMouseExited(e -> applyBtnStyle(startBtn, false));
        startBtn.setOnAction(e -> openDetail(question, startBtn));

        card.getChildren().addAll(topRow, sep, chips, spacer, startBtn);
        return card;
    }

    private Label chip(String text, String bg, String fg) {
        Label l = new Label(text);
        l.setStyle("-fx-background-color: " + bg + "; -fx-text-fill: " + fg + ";" +
                "-fx-font-size: 11px; -fx-font-weight: bold;" +
                "-fx-padding: 3 9; -fx-background-radius: 20;");
        return l;
    }

    private void applyBtnStyle(Button b, boolean hover) {
        b.setStyle("-fx-background-color: " + (hover ? "#388E3C" : "#4CAF50") + ";" +
                "-fx-text-fill: white; -fx-font-size: 13px; -fx-font-weight: bold;" +
                "-fx-padding: 9 18; -fx-background-radius: 28; -fx-cursor: hand;");
    }

    private void openDetail(Question question, Button src) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/QuizDetail.fxml"));
            Parent root = loader.load();

            QuizDetailController ctrl = loader.getController();
            ctrl.initQuestion(question);

            Stage stage = (Stage) src.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle("Quiz — " + question.getTitre());
            stage.setMaximized(true);
            stage.show();

        } catch (IOException e) {
            e.printStackTrace();
            showError("Erreur", "Impossible d'ouvrir le quiz : " + e.getMessage());
        }
    }

    // Navigation methods
    @FXML
    private void handleHome() {
        navigateTo("/fxml/HomePage.fxml", "Path2Learn — Accueil");
    }

    @FXML
    private void handleCours() {
        navigateTo("/fxml/Cours.fxml", "Path2Learn — Cours");
    }

    @FXML
    private void handleRessources() {
        navigateTo("/fxml/Ressources.fxml", "Path2Learn — Ressources");
    }

    @FXML
    private void handleQuestions() {
        // Already on Quiz page
    }

    @FXML
    private void handleProjets() {
        navigateTo("/fxml/Projets.fxml", "Path2Learn — Projets");
    }

    @FXML
    private void handleEvenements() {
        navigateTo("/fxml/Evenements.fxml", "Path2Learn — Événements");
    }

    @FXML
    private void handleUtilisateurs() {
        navigateTo("/fxml/Utilisateurs.fxml", "Path2Learn — Communauté");
    }

    @FXML
    private void handleLogin() {
        navigateTo("/fxml/Login.fxml", "Path2Learn — Connexion");
    }

    @FXML
    private void handleRegister() {
        navigateTo("/fxml/Register.fxml", "Path2Learn — Inscription");
    }

    @FXML
    private void handleGoToAdmin() {
        navigateTo("/fxml/MainMenu.fxml", "Path2Learn — Administration");
    }

    private void navigateTo(String fxmlPath, String title) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();
            Stage stage = (Stage) quizCardsPane.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle(title);
            stage.setMaximized(true);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            showError("Erreur", "Impossible de charger la page : " + e.getMessage());
        }
    }

    private void showError(String title, String msg) {
        Alert a = new Alert(Alert.AlertType.ERROR);
        a.setTitle(title);
        a.setHeaderText(null);
        a.setContentText(msg);
        a.showAndWait();
    }
}