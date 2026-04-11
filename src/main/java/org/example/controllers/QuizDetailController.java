package org.example.controllers;

import javafx.animation.KeyFrame;
import javafx.animation.Timeline;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.stage.Stage;
import javafx.util.Duration;

import org.example.Models.Choix;
import org.example.Models.Question;
import org.example.Services.ReponseService;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;
import java.util.stream.Collectors;

public class QuizDetailController {

    // FXML elements
    @FXML private Label questionTitle;
    @FXML private Label questionDesc;
    @FXML private Label timerLabel;
    @FXML private Label currentScoreLabel;
    @FXML private Label maxScoreLabel;
    @FXML private Label infoLabel;
    @FXML private HBox answeredBanner;
    @FXML private VBox choicesContainer;
    @FXML private Button validateBtn;
    @FXML private Button backToQuizListBtn;
    @FXML private Button backToQuizListBtn2;

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

    @FXML private VBox feedbackCard;
    @FXML private Label feedbackIcon;
    @FXML private Label feedbackTitle;
    @FXML private Label feedbackMessage;
    @FXML private Label feedbackScore;
    @FXML private Label feedbackMaxScore;

    // State
    private Question question;
    private final List<Integer> selectedIds = new ArrayList<>();
    private Timeline countdown;
    private int secondsLeft;
    private boolean answered = false;
    private int correctCount = 0;

    private final ReponseService reponseService = new ReponseService();

    public void initQuestion(Question q) {
        this.question = q;

        questionTitle.setText(q.getTitre());
        questionDesc.setText(q.getDescription() != null && !q.getDescription().isBlank()
                ? q.getDescription() : "");

        maxScoreLabel.setText("/" + (int) q.getNoteMax());
        feedbackMaxScore.setText("/" + (int) q.getNoteMax());
        currentScoreLabel.setText("0");

        // Compter le nombre de bonnes réponses
        correctCount = 0;
        for (Choix choix : q.getChoix()) {
            if (choix.isEstCorrect()) {
                correctCount++;
            }
        }

        infoLabel.setText(correctCount > 1
                ? "Cette question comporte " + correctCount + " bonnes réponses. Sélectionnez-les toutes pour obtenir les points."
                : "Cette question comporte une seule bonne réponse.");

        buildChoices();
        startTimer();
    }

    private void startTimer() {
        if (question.getDuree() > 0) {
            secondsLeft = question.getDuree() * 60;
            refreshTimerLabel();

            countdown = new Timeline(new KeyFrame(Duration.seconds(1), e -> {
                if (answered) return;
                secondsLeft--;
                refreshTimerLabel();
                if (secondsLeft <= 0) onTimeout();
            }));
            countdown.setCycleCount(Timeline.INDEFINITE);
            countdown.play();
        } else {
            timerLabel.setText("∞");
            timerLabel.setStyle("-fx-font-size: 28px; -fx-font-weight: bold; -fx-text-fill: #1A237E;");
        }
    }

    private void refreshTimerLabel() {
        int m = secondsLeft / 60, s = secondsLeft % 60;
        timerLabel.setText(String.format("%d:%02d", m, s));
        String color = secondsLeft <= 10 ? "#C62828" : secondsLeft <= 30 ? "#E65100" : "#1A237E";
        timerLabel.setStyle("-fx-font-size: 28px; -fx-font-weight: bold; -fx-text-fill: " + color + ";");
    }

    private void onTimeout() {
        stopTimer();
        answered = true;
        validateBtn.setDisable(true);
        validateBtn.setText("Temps écoulé");
        lockChoices();
        if (!selectedIds.isEmpty()) {
            evaluateAndShow();
        } else {
            showFeedback("⏰", "Temps écoulé !", "Aucune réponse sélectionnée.", false, 0);
        }
    }

    private void stopTimer() {
        if (countdown != null) countdown.stop();
    }

    private void buildChoices() {
        choicesContainer.getChildren().clear();

        if (question.getChoix() == null || question.getChoix().isEmpty()) {
            Label none = new Label("Aucun choix disponible.");
            none.setStyle("-fx-text-fill: #999; -fx-font-size: 13px;");
            choicesContainer.getChildren().add(none);
            return;
        }

        int idx = 1;
        for (Choix c : question.getChoix()) {
            choicesContainer.getChildren().add(buildRow(c, idx++));
        }
    }

    private HBox buildRow(Choix choix, int index) {
        HBox row = new HBox(14);
        row.setAlignment(Pos.CENTER_LEFT);
        row.setPadding(new Insets(13, 16, 13, 16));
        row.setStyle(STYLE_DEFAULT);

        Label badge = new Label(String.valueOf(index));
        badge.setMinSize(32, 32);
        badge.setMaxSize(32, 32);
        badge.setAlignment(Pos.CENTER);
        badge.setStyle(BADGE_DEFAULT);

        Label text = new Label(choix.getContenu());
        text.setWrapText(true);
        text.setStyle("-fx-font-size: 14px; -fx-text-fill: #333;");
        HBox.setHgrow(text, Priority.ALWAYS);

        boolean[] selected = {false};

        row.setOnMouseClicked(e -> {
            if (answered) return;
            selected[0] = !selected[0];

            if (selected[0]) {
                selectedIds.add(choix.getId());
                row.setStyle(STYLE_SELECTED);
                badge.setStyle(BADGE_SELECTED);
                text.setStyle("-fx-font-size: 14px; -fx-text-fill: #1B5E20; -fx-font-weight: bold;");
            } else {
                selectedIds.remove((Integer) choix.getId());
                row.setStyle(STYLE_DEFAULT);
                badge.setStyle(BADGE_DEFAULT);
                text.setStyle("-fx-font-size: 14px; -fx-text-fill: #333;");
            }

            validateBtn.setDisable(selectedIds.isEmpty());
        });

        row.getChildren().addAll(badge, text);
        row.setUserData(badge);
        return row;
    }

    @FXML
    private void handleValidate() {
        if (selectedIds.isEmpty() || answered) return;
        answered = true;
        stopTimer();
        validateBtn.setDisable(true);
        lockChoices();
        evaluateAndShow();
    }

    /**
     * Calcule le score selon la même logique que votre code Symfony
     *
     * Règles :
     * 1. Aucune réponse cochée → 0 point
     * 2. Une réponse fausse cochée (même si une bonne réponse est cochée) → 0 point
     * 3. Question à choix multiples, utilisateur n'en coche qu'une seule → 0 point
     * 4. EXACTEMENT toutes les bonnes réponses cochées et aucune mauvaise → score max
     * 5. Question à choix unique (une seule bonne réponse) → score max si la bonne est cochée
     */
    private int calculateScore(List<Integer> selectedChoices,
                               List<Integer> correctChoices,
                               int maxScore,
                               int correctCount) {

        // Cas 1: Aucune réponse cochée
        if (selectedChoices.isEmpty()) {
            return 0;
        }

        // Identifier les bonnes et mauvaises réponses sélectionnées
        List<Integer> correctSelected = selectedChoices.stream()
                .filter(correctChoices::contains)
                .collect(Collectors.toList());

        List<Integer> incorrectSelected = selectedChoices.stream()
                .filter(c -> !correctChoices.contains(c))
                .collect(Collectors.toList());

        // Cas 2: Une réponse fausse cochée (même si une bonne réponse est cochée)
        if (!incorrectSelected.isEmpty()) {
            return 0;
        }

        // Cas 3: Question à choix multiples, utilisateur n'en coche qu'une seule
        if (correctCount > 1 && selectedChoices.size() < correctChoices.size()) {
            return 0;
        }

        // Cas 4: EXACTEMENT toutes les bonnes réponses cochées et aucune mauvaise
        if (selectedChoices.size() == correctChoices.size() &&
                correctSelected.size() == correctChoices.size() &&
                incorrectSelected.isEmpty()) {
            return maxScore;
        }

        // Cas 5: Question à choix unique (une seule bonne réponse)
        if (correctCount == 1 && selectedChoices.size() == 1 &&
                correctChoices.contains(selectedChoices.get(0))) {
            return maxScore;
        }

        // Par défaut: 0 point
        return 0;
    }

    private void evaluateAndShow() {
        // Récupérer les IDs des bonnes réponses
        List<Integer> correctIds = question.getChoix().stream()
                .filter(Choix::isEstCorrect)
                .map(Choix::getId)
                .collect(Collectors.toList());

        // Calculer le score selon la logique Symfony
        int score = calculateScore(selectedIds, correctIds, (int) question.getNoteMax(), correctCount);

        // Mettre à jour l'affichage du score
        currentScoreLabel.setText(String.valueOf(score));
        feedbackScore.setText(String.valueOf(score));

        // Colorer les lignes selon les réponses
        colourRows(correctIds);

        // Sauvegarder la réponse
        try {
            reponseService.saveResponse(question, selectedIds, score);
        } catch (Exception ex) {
            System.err.println("[ReponseService] " + ex.getMessage());
        }

        // Déterminer si la réponse est correcte
        boolean isCorrect = score > 0;

        // Construire le message de feedback
        String message;
        String icon;
        String title;

        if (isCorrect) {
            icon = "✅";
            title = "Correct !";
            if (correctCount > 1) {
                message = "Félicitations ! Vous avez sélectionné toutes les bonnes réponses. Score : " + score + "/" + (int) question.getNoteMax();
            } else {
                message = "Félicitations ! Bonne réponse. Score : " + score + "/" + (int) question.getNoteMax();
            }
        } else {
            icon = "❌";
            title = "Incorrect";

            if (selectedIds.isEmpty()) {
                message = "Vous n'avez sélectionné aucune réponse.";
            } else {
                // Vérifier pourquoi la réponse est incorrecte
                List<Integer> incorrectSelected = selectedIds.stream()
                        .filter(id -> !correctIds.contains(id))
                        .collect(Collectors.toList());

                if (!incorrectSelected.isEmpty()) {
                    message = "Dommage ! Vous avez sélectionné une ou plusieurs mauvaises réponses. Score : 0/" + (int) question.getNoteMax();
                } else if (correctCount > 1 && selectedIds.size() < correctIds.size()) {
                    message = "Il fallait sélectionner TOUTES les " + correctCount + " bonnes réponses. Vous n'en avez sélectionné que " + selectedIds.size() + ". Score : 0/" + (int) question.getNoteMax();
                } else {
                    message = "Dommage ! La réponse n'est pas correcte. Score : 0/" + (int) question.getNoteMax();
                }
            }
        }

        showFeedback(icon, title, message, isCorrect, score);
    }

    private void colourRows(List<Integer> correctIds) {
        if (question.getChoix() == null) return;

        for (int i = 0; i < choicesContainer.getChildren().size(); i++) {
            if (!(choicesContainer.getChildren().get(i) instanceof HBox row)) continue;
            Choix choix = question.getChoix().get(i);

            boolean isCorrect = correctIds.contains(choix.getId());
            boolean wasSelected = selectedIds.contains(choix.getId());

            String rowStyle, badgeStyle, badgeText;
            if (isCorrect && wasSelected) {
                rowStyle = STYLE_CORRECT;
                badgeStyle = BADGE_CORRECT;
                badgeText = "✔";
            } else if (isCorrect) {
                rowStyle = STYLE_MISSED;
                badgeStyle = BADGE_MISSED;
                badgeText = "!";
            } else if (wasSelected) {
                rowStyle = STYLE_WRONG;
                badgeStyle = BADGE_WRONG;
                badgeText = "✘";
            } else {
                rowStyle = STYLE_NEUTRAL;
                badgeStyle = BADGE_DEFAULT;
                badgeText = String.valueOf(i + 1);
            }

            row.setStyle(rowStyle);
            if (row.getUserData() instanceof Label badge) {
                badge.setStyle(badgeStyle);
                badge.setText(badgeText);
            }
        }
    }

    private void showFeedback(String icon, String title, String msg, boolean ok, int score) {
        feedbackIcon.setText(icon);
        feedbackTitle.setText(title);
        feedbackTitle.setStyle("-fx-font-size: 21px; -fx-font-weight: bold; -fx-text-fill: " +
                (ok ? "#2E7D32" : "#C62828") + ";");
        feedbackMessage.setText(msg);
        feedbackScore.setText(String.valueOf(score));

        feedbackCard.setOpacity(0);
        feedbackCard.setVisible(true);
        feedbackCard.setManaged(true);

        Timeline fade = new Timeline(
                new KeyFrame(Duration.ZERO, e -> feedbackCard.setOpacity(0)),
                new KeyFrame(Duration.millis(350), e -> feedbackCard.setOpacity(1))
        );
        fade.play();

        validateBtn.setVisible(false);
        validateBtn.setManaged(false);
    }

    private void lockChoices() {
        for (javafx.scene.Node n : choicesContainer.getChildren()) {
            if (n instanceof HBox row) {
                row.setOnMouseClicked(null);
                row.setStyle(row.getStyle() + " -fx-cursor: default;");
            }
        }
    }

    // Navigation methods
    @FXML
    private void handleBackToQuizList() {
        stopTimer();
        navigateTo("/fxml/QuizList.fxml", "Path2Learn — Quiz");
    }

    @FXML
    private void handleHome() {
        stopTimer();
        navigateTo("/fxml/HomePage.fxml", "Path2Learn — Accueil");
    }

    @FXML
    private void handleCours() {
        stopTimer();
        navigateTo("/fxml/Cours.fxml", "Path2Learn — Cours");
    }

    @FXML
    private void handleRessources() {
        stopTimer();
        navigateTo("/fxml/Ressources.fxml", "Path2Learn — Ressources");
    }

    @FXML
    private void handleQuestions() {
        stopTimer();
        navigateTo("/fxml/QuizList.fxml", "Path2Learn — Quiz");
    }

    @FXML
    private void handleProjets() {
        stopTimer();
        navigateTo("/fxml/Projets.fxml", "Path2Learn — Projets");
    }

    @FXML
    private void handleEvenements() {
        stopTimer();
        navigateTo("/fxml/Evenements.fxml", "Path2Learn — Événements");
    }

    @FXML
    private void handleUtilisateurs() {
        stopTimer();
        navigateTo("/fxml/Utilisateurs.fxml", "Path2Learn — Communauté");
    }

    @FXML
    private void handleLogin() {
        stopTimer();
        navigateTo("/fxml/Login.fxml", "Path2Learn — Connexion");
    }

    @FXML
    private void handleRegister() {
        stopTimer();
        navigateTo("/fxml/Register.fxml", "Path2Learn — Inscription");
    }

    @FXML
    private void handleGoToAdmin() {
        stopTimer();
        navigateTo("/fxml/MainMenu.fxml", "Path2Learn — Administration");
    }

    private void navigateTo(String fxmlPath, String title) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();
            Stage stage = (Stage) validateBtn.getScene().getWindow();
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

    // Style constants
    private static final String STYLE_DEFAULT = "-fx-background-color: white; -fx-background-radius: 12;" +
            "-fx-effect: dropshadow(three-pass-box,rgba(0,0,0,0.07),8,0,0,2);-fx-cursor: hand;";
    private static final String STYLE_SELECTED = "-fx-background-color: #E8F5E9; -fx-background-radius: 12;" +
            "-fx-border-color: #4CAF50; -fx-border-radius: 12; -fx-border-width: 2;-fx-cursor: hand;";
    private static final String STYLE_CORRECT = "-fx-background-color: #E8F5E9; -fx-background-radius: 12;" +
            "-fx-border-color: #388E3C; -fx-border-radius: 12; -fx-border-width: 2;";
    private static final String STYLE_MISSED = "-fx-background-color: #FFF8E1; -fx-background-radius: 12;" +
            "-fx-border-color: #F57F17; -fx-border-radius: 12; -fx-border-width: 2;";
    private static final String STYLE_WRONG = "-fx-background-color: #FFEBEE; -fx-background-radius: 12;" +
            "-fx-border-color: #C62828; -fx-border-radius: 12; -fx-border-width: 2;";
    private static final String STYLE_NEUTRAL = "-fx-background-color: #FAFAFA; -fx-background-radius: 12;" +
            "-fx-border-color: #E0E0E0; -fx-border-radius: 12; -fx-border-width: 1;";

    private static final String BADGE_DEFAULT = "-fx-background-color: #E8F5E9; -fx-text-fill: #2E7D32;" +
            "-fx-font-weight: bold; -fx-font-size: 12px; -fx-background-radius: 16;";
    private static final String BADGE_SELECTED = "-fx-background-color: #4CAF50; -fx-text-fill: white;" +
            "-fx-font-weight: bold; -fx-font-size: 12px; -fx-background-radius: 16;";
    private static final String BADGE_CORRECT = "-fx-background-color: #388E3C; -fx-text-fill: white;" +
            "-fx-font-weight: bold; -fx-font-size: 12px; -fx-background-radius: 16;";
    private static final String BADGE_MISSED = "-fx-background-color: #F57F17; -fx-text-fill: white;" +
            "-fx-font-weight: bold; -fx-font-size: 12px; -fx-background-radius: 16;";
    private static final String BADGE_WRONG = "-fx-background-color: #C62828; -fx-text-fill: white;" +
            "-fx-font-weight: bold; -fx-font-size: 12px; -fx-background-radius: 16;";
}