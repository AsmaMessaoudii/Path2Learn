package org.example.controllers;

import javafx.collections.FXCollections;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.stage.Stage;
import org.example.Models.Choix;
import org.example.Models.Question;
import org.example.Services.ChoixService;
import org.example.Services.QuestionService;
import org.example.validators.ChoixValidator;
import java.io.IOException;
import java.sql.SQLException;
import java.util.List;

public class ChoixAddController {

    @FXML private TextArea contenuArea;
    @FXML private RadioButton correctOui;
    @FXML private RadioButton correctNon;
    @FXML private ComboBox<String> questionCombo;
    @FXML private TextField questionIdField;
    @FXML private Label statusLabel;
    @FXML private Label contenuErrorLabel;
    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;

    private ChoixService choixService;
    private QuestionService questionService;
    private ChoixValidator validator;
    private List<Question> questions;
    private int defaultQuestionId;
    private String defaultQuestionTitle;


    @FXML
    public void initialize() {
        choixService = new ChoixService();
        questionService = new QuestionService();
        validator = new ChoixValidator();
        correctNon.setSelected(true);
        loadQuestions();

        contenuArea.textProperty().addListener((obs, oldVal, newVal) -> validateContenu());

        questionCombo.setOnAction(e -> {
            String selected = questionCombo.getSelectionModel().getSelectedItem();
            if (selected != null) {
                for (Question q : questions) {
                    if (selected.equals(q.getId() + " - " + q.getTitre())) {
                        questionIdField.setText(String.valueOf(q.getId()));
                        break;
                    }
                }
            }
        });

        questionIdField.textProperty().addListener((obs, oldVal, newVal) -> {
            if (!newVal.isEmpty()) {
                try {
                    int id = Integer.parseInt(newVal);
                    for (Question q : questions) {
                        if (q.getId() == id) {
                            questionCombo.getSelectionModel().select(q.getId() + " - " + q.getTitre());
                            break;
                        }
                    }
                } catch (NumberFormatException e) {
                    // Ignorer
                }
            }
        });
    }

    private void validateContenu() {
        String contenu = contenuArea.getText();
        if (contenu == null || contenu.trim().isEmpty()) {
            contenuErrorLabel.setText("❌ Le contenu est obligatoire");
            contenuErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        } else if (contenu.length() < 1) {
            contenuErrorLabel.setText("❌ L'option doit contenir au moins 1 caractère");
            contenuErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        } else if (contenu.length() > 500) {
            contenuErrorLabel.setText("❌ L'option ne doit pas dépasser 500 caractères");
            contenuErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        } else if (!contenu.matches("^[a-zA-Z0-9\\s\\p{L}À-ÿ\\-\\'\\?\\!\\,\\.]+$")) {
            contenuErrorLabel.setText("❌ Caractères non autorisés");
            contenuErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        } else {
            contenuErrorLabel.setText("✅ Contenu valide");
            contenuErrorLabel.setStyle("-fx-text-fill: green; -fx-font-size: 11px;");
        }
    }

    public void setQuestionId(int questionId, String questionTitle) {
        this.defaultQuestionId = questionId;
        this.defaultQuestionTitle = questionTitle;
        questionIdField.setText(String.valueOf(questionId));
        questionIdField.setDisable(true);
        questionCombo.setDisable(true);
    }

    private void loadQuestions() {
        try {
            questions = questionService.recuperer();
            for (Question q : questions) {
                questionCombo.getItems().add(q.getId() + " - " + q.getTitre());
            }
        } catch (SQLException e) {
            showError("Erreur: " + e.getMessage());
        }
    }

    @FXML
    private void handleAjouter(ActionEvent event) {
        validateContenu();

        if (contenuArea.getText().trim().isEmpty()) {
            showError("Le contenu de l'option est obligatoire");
            return;
        }

        try {
            Choix choix = new Choix();
            choix.setContenu(contenuArea.getText());
            choix.setEstCorrect(correctOui.isSelected());

            int questionId;
            if (!questionIdField.getText().isEmpty()) {
                questionId = Integer.parseInt(questionIdField.getText());
            } else if (questionCombo.getSelectionModel().getSelectedItem() != null) {
                String selected = questionCombo.getSelectionModel().getSelectedItem();
                questionId = Integer.parseInt(selected.split(" - ")[0]);
            } else {
                showError("Veuillez sélectionner une question");
                return;
            }
            choix.setQuestionId(questionId);

            if (!validator.validate(choix)) {
                showError("Erreurs de validation:\n• " + validator.getErrorsAsString());
                return;
            }

            choixService.ajouter(choix);

            Alert alert = new Alert(Alert.AlertType.INFORMATION);
            alert.setTitle("Succès");
            alert.setHeaderText(null);
            alert.setContentText("Option ajoutée avec succès !");
            alert.showAndWait();

            handleRetour(event);

        } catch (SQLException | NumberFormatException e) {
            showError("Erreur: " + e.getMessage());
        }
    }

    @FXML
    private void handleAnnuler(ActionEvent event) {
        clearForm();
    }

    @FXML
    private void handleRetour(ActionEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/ChoixListView.fxml"));
            Parent root = loader.load();

            ChoixListController controller = loader.getController();
            Question q = new Question();
            q.setId(defaultQuestionId != 0 ? defaultQuestionId : Integer.parseInt(questionIdField.getText()));
            q.setTitre(defaultQuestionTitle);
            controller.setQuestion(q);

            Scene scene = contenuArea.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            showError("Erreur lors du retour");
        }
    }

    private void clearForm() {
        contenuArea.clear();
        correctNon.setSelected(true);
        contenuErrorLabel.setText("");
        if (defaultQuestionId == 0) {
            questionCombo.getSelectionModel().clearSelection();
            questionIdField.clear();
        }
        statusLabel.setText("Formulaire réinitialisé");
        statusLabel.setStyle("-fx-text-fill: blue;");
    }

    private void showError(String message) {
        statusLabel.setText("❌ " + message);
        statusLabel.setStyle("-fx-text-fill: red;");

        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle("Erreur");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    // ==================== MÉTHODES DE NAVIGATION ====================

    @FXML
    private void handleHome() {
        naviguerVers("/fxml/MainMenu.fxml", "Path2Learn - Accueil");
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
        naviguerVers("/fxml/PortfolioListView.fxml", "Path2Learn - Portfolios");
    }

    @FXML
    private void handleEvenements() {
        showInfoAlert("Événements", "Module Événements - Bientôt disponible");
    }

    @FXML
    private void handleUtilisateurs() {
        naviguerVers("/fxml/User.fxml", "Path2Learn - Utilisateurs");
    }

    // ==================== MÉTHODES UTILITAIRES DE NAVIGATION ====================

    private void naviguerVers(String fxmlPath, String titre) {
        try {
            java.net.URL resource = getClass().getResource(fxmlPath);
            if (resource == null) {
                showError("Page non trouvée: " + fxmlPath);
                return;
            }
            FXMLLoader loader = new FXMLLoader(resource);
            Parent root = loader.load();
            Stage stage = (Stage) contenuArea.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle(titre);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            showError("Erreur de navigation: " + e.getMessage());
        }
    }

    private void showInfoAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}