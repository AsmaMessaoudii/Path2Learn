package org.example.controllers;

import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.stage.Stage;
import org.example.Models.Choix;
import org.example.Models.Question;
import org.example.Services.ChoixService;
import org.example.Services.QuestionService;
import java.io.IOException;
import java.sql.SQLException;
import java.util.Optional;

public class ChoixListController {

    @FXML private TableView<Choix> choixTable;
    @FXML private TableColumn<Choix, Integer> colId;
    @FXML private TableColumn<Choix, String> colContenu;
    @FXML private TableColumn<Choix, Boolean> colEstCorrect;
    @FXML private TableColumn<Choix, Integer> colQuestionId;

    @FXML private Button btnModifier;
    @FXML private Button btnSupprimer;
    @FXML private TextField searchField;
    @FXML private Label statusLabel;
    @FXML private Label pageLabel;
    @FXML private Label questionInfoLabel;
    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;

    private ChoixService choixService;
    private QuestionService questionService;
    private ObservableList<Choix> choixList;
    private ObservableList<Choix> filteredList;
    private Choix selectedChoix;
    private int currentPage = 0;
    private int itemsPerPage = 10;
    private int currentQuestionId;
    private String currentQuestionTitle;

    @FXML
    public void initialize() {
        System.out.println("Initialisation de ChoixListController...");

        choixService = new ChoixService();
        questionService = new QuestionService();
        choixList = FXCollections.observableArrayList();
        filteredList = FXCollections.observableArrayList();

        // Initialiser les colonnes
        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colContenu.setCellValueFactory(new PropertyValueFactory<>("contenu"));
        colEstCorrect.setCellValueFactory(new PropertyValueFactory<>("estCorrect"));
        colQuestionId.setCellValueFactory(new PropertyValueFactory<>("questionId"));

        // Formater la colonne booléenne
        colEstCorrect.setCellFactory(column -> new TableCell<Choix, Boolean>() {
            @Override
            protected void updateItem(Boolean item, boolean empty) {
                super.updateItem(item, empty);
                if (empty || item == null) {
                    setText(null);
                } else {
                    setText(item ? "✅ Oui" : "❌ Non");
                    setStyle(item ? "-fx-text-fill: green; -fx-font-weight: bold;" : "-fx-text-fill: red;");
                }
            }
        });

        // Listener pour la sélection
        choixTable.getSelectionModel().selectedItemProperty().addListener((obs, oldSelection, newSelection) -> {
            if (newSelection != null) {
                selectedChoix = newSelection;
                btnModifier.setDisable(false);
                btnSupprimer.setDisable(false);
            } else {
                btnModifier.setDisable(true);
                btnSupprimer.setDisable(true);
            }
        });

        btnModifier.setDisable(true);
        btnSupprimer.setDisable(true);
    }

    public void setQuestion(Question question) {
        System.out.println("setQuestion appelé avec ID: " + question.getId());
        this.currentQuestionId = question.getId();
        this.currentQuestionTitle = question.getTitre();
        if (questionInfoLabel != null) {
            questionInfoLabel.setText("#" + currentQuestionId + " - " + currentQuestionTitle);
        }
        loadChoix();
    }

    private void loadChoix() {
        try {
            choixList.clear();
            System.out.println("Chargement des choix pour la question ID: " + currentQuestionId);

            for (Choix c : choixService.recuperer()) {
                if (c.getQuestionId() == currentQuestionId) {
                    choixList.add(c);
                }
            }

            filteredList.setAll(choixList);
            updateTable();
            statusLabel.setText("✅ " + choixList.size() + " option(s) chargée(s)");
            statusLabel.setStyle("-fx-text-fill: green;");
            System.out.println("Choix chargés: " + choixList.size());

        } catch (SQLException e) {
            showError("Erreur lors du chargement: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private void updateTable() {
        int start = currentPage * itemsPerPage;
        int end = Math.min(start + itemsPerPage, filteredList.size());

        if (start < filteredList.size()) {
            choixTable.setItems(FXCollections.observableArrayList(filteredList.subList(start, end)));
            int totalPages = (int) Math.ceil((double) filteredList.size() / itemsPerPage);
            pageLabel.setText("Page " + (currentPage + 1) + " sur " + Math.max(1, totalPages));
        } else {
            if (currentPage > 0 && filteredList.size() > 0) {
                currentPage = 0;
                updateTable();
            } else {
                choixTable.setItems(FXCollections.observableArrayList());
                pageLabel.setText("Page 0 sur 0");
            }
        }
    }

    @FXML
    private void handleAjouter(ActionEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/ChoixAddView.fxml"));
            Parent root = loader.load();

            ChoixAddController controller = loader.getController();
            controller.setQuestionId(currentQuestionId, currentQuestionTitle);

            Scene scene = btnModifier.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            showError("Erreur: " + e.getMessage());
            e.printStackTrace();
        }
    }

    @FXML
    private void handleModifier(ActionEvent event) {
        if (selectedChoix == null) {
            showError("Veuillez sélectionner une option");
            return;
        }

        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/ChoixEditView.fxml"));
            Parent root = loader.load();

            ChoixEditController controller = loader.getController();
            controller.setChoix(selectedChoix);

            Scene scene = btnModifier.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            e.printStackTrace();
            showError("Erreur: " + e.getMessage());
        }
    }

    @FXML
    private void handleSupprimer(ActionEvent event) {
        if (selectedChoix == null) {
            showError("Veuillez sélectionner une option");
            return;
        }

        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation de suppression");
        alert.setHeaderText("Supprimer l'option");
        alert.setContentText("Êtes-vous sûr de vouloir supprimer l'option : \n\n" +
                selectedChoix.getContenu() + "\n\nCette action est irréversible !");

        Optional<ButtonType> result = alert.showAndWait();
        if (result.isPresent() && result.get() == ButtonType.OK) {
            try {
                choixService.supprimer(selectedChoix);
                loadChoix();
                showSuccess("Option supprimée avec succès!");
                selectedChoix = null;
            } catch (SQLException e) {
                showError("Erreur lors de la suppression: " + e.getMessage());
            }
        }
    }

    private void showSuccess(String message) {
        statusLabel.setText("✅ " + message);
        statusLabel.setStyle("-fx-text-fill: green;");
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Succès");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    @FXML
    private void handleActualiser(ActionEvent event) {
        loadChoix();
        searchField.clear();
    }

    @FXML
    private void handleRechercher(ActionEvent event) {
        String searchText = searchField.getText().toLowerCase();
        if (searchText.isEmpty()) {
            filteredList.setAll(choixList);
        } else {
            filteredList.setAll(choixList.filtered(c ->
                    c.getContenu().toLowerCase().contains(searchText)
            ));
        }
        currentPage = 0;
        updateTable();
        statusLabel.setText("🔍 " + filteredList.size() + " résultat(s) trouvé(s)");
    }

    @FXML
    private void handlePrevious(ActionEvent event) {
        if (currentPage > 0) {
            currentPage--;
            updateTable();
        }
    }

    @FXML
    private void handleNext(ActionEvent event) {
        if ((currentPage + 1) * itemsPerPage < filteredList.size()) {
            currentPage++;
            updateTable();
        }
    }

    @FXML
    private void handleRetourQuestions(ActionEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/QuestionListView.fxml"));
            Parent root = loader.load();
            Scene scene = choixTable.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            showError("Erreur lors du retour");
            e.printStackTrace();
        }
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
        naviguerVers("/fxml/PortfolioListViewBack.fxml", "Path2Learn - Portfolios");
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
            // CORRECTION: Utiliser choixTable au lieu de contenuArea
            Stage stage = (Stage) choixTable.getScene().getWindow();
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

    private void showError(String message) {
        statusLabel.setText("❌ " + message);
        statusLabel.setStyle("-fx-text-fill: red;");
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle("Erreur");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}