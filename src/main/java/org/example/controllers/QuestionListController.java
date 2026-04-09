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
import org.example.Models.Question;
import org.example.Services.QuestionService;
import java.io.IOException;
import java.sql.SQLException;
import java.util.Optional;

public class QuestionListController {

    @FXML private TableView<Question> questionTable;
    @FXML private TableColumn<Question, Integer> colId;
    @FXML private TableColumn<Question, String> colTitre;
    @FXML private TableColumn<Question, String> colDescription;
    @FXML private TableColumn<Question, Integer> colDuree;
    @FXML private TableColumn<Question, Float> colNoteMax;
    @FXML private TableColumn<Question, Integer> colUserId;

    @FXML private Button btnModifier;
    @FXML private Button btnSupprimer;
    @FXML private TextField searchField;
    @FXML private Label statusLabel;
    @FXML private Label pageLabel;
    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;

    private QuestionService questionService;
    private ObservableList<Question> questionList;
    private ObservableList<Question> filteredList;
    private Question selectedQuestion;
    private int currentPage = 0;
    private int itemsPerPage = 10;

    @FXML
    public void initialize() {
        System.out.println("Initialisation de QuestionListController...");

        questionService = new QuestionService();
        questionList = FXCollections.observableArrayList();
        filteredList = FXCollections.observableArrayList();

        // Initialiser les colonnes
        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colTitre.setCellValueFactory(new PropertyValueFactory<>("titre"));
        colDescription.setCellValueFactory(new PropertyValueFactory<>("description"));
        colDuree.setCellValueFactory(new PropertyValueFactory<>("duree"));
        colNoteMax.setCellValueFactory(new PropertyValueFactory<>("noteMax"));
        colUserId.setCellValueFactory(new PropertyValueFactory<>("userId"));

        // Charger les questions
        loadQuestions();

        // Listener pour la sélection
        questionTable.getSelectionModel().selectedItemProperty().addListener((obs, oldSelection, newSelection) -> {
            if (newSelection != null) {
                selectedQuestion = newSelection;
                btnModifier.setDisable(false);
                btnSupprimer.setDisable(false);
            } else {
                btnModifier.setDisable(true);
                btnSupprimer.setDisable(true);
            }
        });

        btnModifier.setDisable(true);
        btnSupprimer.setDisable(true);

        // Ajouter la colonne d'actions APRÈS avoir chargé les données
        addActionsColumn();
    }

    private void addActionsColumn() {
        TableColumn<Question, Void> colActions = new TableColumn<>("Actions");
        colActions.setPrefWidth(140);

        colActions.setCellFactory(column -> new TableCell<Question, Void>() {
            private final Button manageButton = new Button("⚙️ Gérer les options");

            {
                manageButton.setStyle("-fx-background-color: #42A5F5; -fx-text-fill: white; -fx-font-size: 11px; -fx-cursor: hand; -fx-padding: 5 10; -fx-background-radius: 3;");
                manageButton.setOnAction(event -> {
                    Question question = getTableView().getItems().get(getIndex());
                    System.out.println("Clic sur Gérer options pour la question ID: " + question.getId());
                    gererOptionsPourQuestion(question);
                });
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                if (empty) {
                    setGraphic(null);
                } else {
                    setGraphic(manageButton);
                }
            }
        });

        questionTable.getColumns().add(colActions);
    }

    private void loadQuestions() {
        try {
            questionList.clear();
            questionList.addAll(questionService.recuperer());
            filteredList.setAll(questionList);
            updateTable();
            statusLabel.setText("✅ " + questionList.size() + " question(s) chargée(s)");
            statusLabel.setStyle("-fx-text-fill: green;");
            System.out.println("Questions chargées: " + questionList.size());
        } catch (SQLException e) {
            showError("Erreur lors du chargement: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private void updateTable() {
        int start = currentPage * itemsPerPage;
        int end = Math.min(start + itemsPerPage, filteredList.size());

        if (start < filteredList.size()) {
            questionTable.setItems(FXCollections.observableArrayList(filteredList.subList(start, end)));
            int totalPages = (int) Math.ceil((double) filteredList.size() / itemsPerPage);
            pageLabel.setText("Page " + (currentPage + 1) + " sur " + Math.max(1, totalPages));
        } else {
            currentPage = 0;
            updateTable();
        }
    }

    @FXML
    private void handleAjouter(ActionEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/QuestionAddView.fxml"));
            Parent root = loader.load();
            Scene scene = btnModifier.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            showError("Erreur: " + e.getMessage());
            e.printStackTrace();
        }
    }

    @FXML
    private void handleModifier(ActionEvent event) {
        if (selectedQuestion == null) {
            showError("Veuillez sélectionner une question");
            return;
        }

        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/QuestionEditView.fxml"));
            Parent root = loader.load();

            QuestionEditController controller = loader.getController();
            controller.setQuestion(selectedQuestion);

            Scene scene = btnModifier.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            e.printStackTrace();
            showError("Erreur: " + e.getMessage());
        }
    }

    @FXML
    private void handleSupprimer(ActionEvent event) {
        if (selectedQuestion == null) {
            showError("Veuillez sélectionner une question");
            return;
        }

        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation de suppression");
        alert.setHeaderText("Supprimer la question");
        alert.setContentText("Êtes-vous sûr de vouloir supprimer la question : \n\n" +
                selectedQuestion.getTitre() + "\n\n⚠️ Attention : Toutes les options associées seront également supprimées !\n\nCette action est irréversible !");

        Optional<ButtonType> result = alert.showAndWait();
        if (result.isPresent() && result.get() == ButtonType.OK) {
            try {
                questionService.supprimer(selectedQuestion);
                loadQuestions();
                showSuccess("Question supprimée avec succès!");
                selectedQuestion = null;
            } catch (SQLException e) {
                showError("Erreur lors de la suppression: " + e.getMessage());
            }
        }
    }

    // Méthode pour gérer les options d'une question spécifique
    private void gererOptionsPourQuestion(Question question) {
        try {
            System.out.println("=== Chargement des options ===");
            System.out.println("Question ID: " + question.getId());
            System.out.println("Question Titre: " + question.getTitre());
            System.out.println("Chemin du FXML: /fxml/ChoixListView.fxml");

            // Vérifier si le fichier FXML existe
            java.net.URL fxmlUrl = getClass().getResource("/fxml/ChoixListView.fxml");
            if (fxmlUrl == null) {
                System.err.println("ERREUR: Fichier ChoixListView.fxml non trouvé !");
                showError("Fichier ChoixListView.fxml non trouvé !");
                return;
            }
            System.out.println("FXML trouvé à: " + fxmlUrl.getPath());

            FXMLLoader loader = new FXMLLoader(fxmlUrl);
            Parent root = loader.load();

            ChoixListController controller = loader.getController();
            if (controller == null) {
                System.err.println("ERREUR: Le contrôleur ChoixListController est null !");
                showError("Erreur de chargement du contrôleur");
                return;
            }

            controller.setQuestion(question);

            Scene scene = questionTable.getScene();
            scene.setRoot(root);

            System.out.println("Navigation vers ChoixListController réussie !");

        } catch (IOException e) {
            System.err.println("ERREUR IO: " + e.getMessage());
            e.printStackTrace();
            showError("Erreur lors du chargement de la gestion des options: " + e.getMessage());
        } catch (Exception e) {
            System.err.println("ERREUR: " + e.getMessage());
            e.printStackTrace();
            showError("Erreur inattendue: " + e.getMessage());
        }
    }

    @FXML
    private void handleActualiser(ActionEvent event) {
        loadQuestions();
        searchField.clear();
    }

    @FXML
    private void handleRechercher(ActionEvent event) {
        String searchText = searchField.getText().toLowerCase();
        if (searchText.isEmpty()) {
            filteredList.setAll(questionList);
        } else {
            filteredList.setAll(questionList.filtered(q ->
                    q.getTitre().toLowerCase().contains(searchText) ||
                            q.getDescription().toLowerCase().contains(searchText)
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

    // Navigation Menu
    @FXML
    private void handleHome() { navigateTo("/fxml/MainMenu.fxml"); }

    @FXML
    private void handleCours() { showAlert("Cours", "Page en construction"); }

    @FXML
    private void handleRessources() { showAlert("Ressources", "Page en construction"); }

    @FXML
    private void handleQuestions() { /* Déjà sur la page des questions */ }

    @FXML
    private void handleProjets() { showAlert("Projets", "Page en construction"); }

    @FXML
    private void handleEvenements() { showAlert("Événements", "Page en construction"); }

    @FXML
    private void handleUtilisateurs() { showAlert("Communauté", "Page en construction"); }

    private void navigateTo(String fxmlPath) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();
            Scene scene = questionTable.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            showError("Erreur de navigation: " + e.getMessage());
        }
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

    private void showSuccess(String message) {
        statusLabel.setText("✅ " + message);
        statusLabel.setStyle("-fx-text-fill: green;");
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}