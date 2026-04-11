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

        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colTitre.setCellValueFactory(new PropertyValueFactory<>("titre"));
        colDescription.setCellValueFactory(new PropertyValueFactory<>("description"));
        colDuree.setCellValueFactory(new PropertyValueFactory<>("duree"));
        colNoteMax.setCellValueFactory(new PropertyValueFactory<>("noteMax"));
        colUserId.setCellValueFactory(new PropertyValueFactory<>("userId"));

        loadQuestions();

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
        } catch (SQLException e) {
            showError("Erreur lors du chargement: " + e.getMessage());
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
        navigateTo("/fxml/QuestionAddView.fxml");
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
        alert.setContentText("Supprimer : " + selectedQuestion.getTitre() + " ?\n\n⚠️ Toutes les options seront supprimées !");

        if (alert.showAndWait().orElse(ButtonType.CANCEL) == ButtonType.OK) {
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

    private void gererOptionsPourQuestion(Question question) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/ChoixListView.fxml"));
            Parent root = loader.load();
            ChoixListController controller = loader.getController();
            controller.setQuestion(question);
            Scene scene = questionTable.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            showError("Erreur lors du chargement des options: " + e.getMessage());
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

    // ==================== NAVIGATION CORRIGÉE ====================
    @FXML private void handleHome() { navigateTo("/fxml/MainMenu.fxml"); }
    @FXML private void handleCours() { navigateTo("/fxml/CoursView.fxml"); }
    @FXML private void handleRessources() { navigateTo("/fxml/RessourcesView.fxml"); }
    @FXML private void handleQuestions() { navigateTo("/fxml/QuestionListView.fxml"); }
    @FXML private void handleProjets() { navigateTo("/fxml/PortfolioListView.fxml"); }
    @FXML private void handleEvenements() { showInfo("Événements", "Module en construction"); }
    @FXML private void handleUtilisateurs() { navigateTo("/fxml/User.fxml"); }

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

    private void showInfo(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}