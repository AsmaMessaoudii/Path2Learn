package org.example.controllers;

import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.collections.transformation.FilteredList;
import javafx.collections.transformation.SortedList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;
import javafx.stage.Modality;
import javafx.stage.Stage;
import org.example.Models.Portfolio;
import org.example.Services.ServicePortfolio;

import java.io.IOException;
import java.util.Optional;

public class PortfolioListController {

    @FXML private TableView<Portfolio> tableView;
    @FXML private TableColumn<Portfolio, Integer> colId;
    @FXML private TableColumn<Portfolio, String> colTitre;
    @FXML private TableColumn<Portfolio, String> colDescription;
    @FXML private TableColumn<Portfolio, String> colDateCreation;
    @FXML private TableColumn<Portfolio, String> colDateMiseAjour;
    @FXML private TableColumn<Portfolio, Integer> colUserId;
    @FXML private TableColumn<Portfolio, Void> colActions;

    @FXML private TextField searchField;
    @FXML private Label statsLabel;
    @FXML private Button homeBtn;

    private ServicePortfolio servicePortfolio;
    private ObservableList<Portfolio> portfolioList;
    private FilteredList<Portfolio> filteredList;

    @FXML
    public void initialize() {
        servicePortfolio = new ServicePortfolio();
        setupTableColumns();
        chargerPortfolios();
        setupSearchFilter();
        setupActionButtons();
    }

    // ==================== NAVIGATION ====================

    @FXML private void handleHome() { naviguerVers("/fxml/MainMenu.fxml", "Path2Learn - Accueil"); }
    @FXML private void handleCours() { naviguerVers("/fxml/CoursView.fxml", "Path2Learn - Cours"); }
    @FXML private void handleRessources() { naviguerVers("/fxml/RessourcesView.fxml", "Path2Learn - Ressources"); }
    @FXML private void handleQuestions() { naviguerVers("/fxml/QuestionListView.fxml", "Path2Learn - Quiz"); }
    @FXML private void handleProjets() { chargerPortfolios(); }
    @FXML private void handleEvenements() { showAlert("Événements", "Bientôt disponible", Alert.AlertType.INFORMATION); }
    @FXML private void handleUtilisateurs() { naviguerVers("/fxml/User.fxml", "Path2Learn - Utilisateurs"); }

    private void naviguerVers(String fxmlPath, String titre) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();
            Stage stage = (Stage) homeBtn.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle(titre);
            stage.show();
        } catch (IOException e) {
            showAlert("Erreur", "Impossible de charger la page: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    // ==================== TABLE ====================

    private void setupTableColumns() {
        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colTitre.setCellValueFactory(new PropertyValueFactory<>("titre"));
        colDescription.setCellValueFactory(new PropertyValueFactory<>("description"));
        colDateCreation.setCellValueFactory(new PropertyValueFactory<>("dateCreation"));
        colDateMiseAjour.setCellValueFactory(new PropertyValueFactory<>("dateMiseAjour"));
        colUserId.setCellValueFactory(new PropertyValueFactory<>("userId"));
    }

    private void chargerPortfolios() {
        try {
            portfolioList = FXCollections.observableArrayList(servicePortfolio.recuperer());
            filteredList = new FilteredList<>(portfolioList, p -> true);
            SortedList<Portfolio> sortedList = new SortedList<>(filteredList);
            sortedList.comparatorProperty().bind(tableView.comparatorProperty());
            tableView.setItems(sortedList);
            updateStats();
        } catch (Exception e) {
            showAlert("Erreur", "Impossible de charger les portfolios: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void setupSearchFilter() {
        searchField.textProperty().addListener((observable, oldValue, newValue) -> {
            filteredList.setPredicate(portfolio -> {
                if (newValue == null || newValue.isEmpty()) return true;
                String lower = newValue.toLowerCase();
                return portfolio.getTitre().toLowerCase().contains(lower) ||
                        portfolio.getDescription().toLowerCase().contains(lower);
            });
            updateStats();
        });
    }

    private void setupActionButtons() {
        colActions.setCellFactory(col -> new TableCell<Portfolio, Void>() {
            private final Button editBtn = new Button("✏️ Modifier");
            private final Button deleteBtn = new Button("🗑️ Supprimer");
            private final Button projetsBtn = new Button("📁 Voir projets");
            private final HBox buttons = new HBox(8, editBtn, deleteBtn, projetsBtn);

            {
                editBtn.setStyle("-fx-background-color: #2196F3; -fx-text-fill: white; -fx-font-size: 11px; -fx-padding: 5 10; -fx-background-radius: 5; -fx-cursor: hand;");
                deleteBtn.setStyle("-fx-background-color: #f44336; -fx-text-fill: white; -fx-font-size: 11px; -fx-padding: 5 10; -fx-background-radius: 5; -fx-cursor: hand;");
                projetsBtn.setStyle("-fx-background-color: #81C784; -fx-text-fill: white; -fx-font-size: 11px; -fx-padding: 5 10; -fx-background-radius: 5; -fx-cursor: hand;");
                buttons.setAlignment(Pos.CENTER);
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                if (empty) {
                    setGraphic(null);
                } else {
                    Portfolio portfolio = getTableView().getItems().get(getIndex());
                    editBtn.setOnAction(e -> ouvrirDialog(portfolio));
                    deleteBtn.setOnAction(e -> handleSupprimer(portfolio));
                    projetsBtn.setOnAction(e -> voirProjets(portfolio));
                    setGraphic(buttons);
                }
            }
        });
    }

    // ==================== CRUD ====================

    @FXML
    private void handleAjouter() {
        ouvrirDialog(null);
    }

    private void ouvrirDialog(Portfolio portfolio) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/PortfolioDialog.fxml"));
            VBox dialogVBox = loader.load();

            PortfolioDialogController dialogController = loader.getController();
            dialogController.setServicePortfolio(servicePortfolio);
            dialogController.setPortfolio(portfolio);
            dialogController.setParentController(this);

            Stage dialogStage = new Stage();
            dialogStage.initModality(Modality.APPLICATION_MODAL);
            dialogStage.setTitle(portfolio == null ? "Ajouter un portfolio" : "Modifier un portfolio");
            dialogStage.setScene(new Scene(dialogVBox));
            dialogStage.showAndWait();

        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible d'ouvrir le dialogue: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void handleSupprimer(Portfolio portfolio) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation");
        alert.setHeaderText("Supprimer le portfolio");
        alert.setContentText("Voulez-vous vraiment supprimer : " + portfolio.getTitre() + " ?");

        Optional<ButtonType> result = alert.showAndWait();
        if (result.isPresent() && result.get() == ButtonType.OK) {
            try {
                servicePortfolio.supprimer(portfolio);
                chargerPortfolios();
                showAlert("Succès", "Portfolio supprimé avec succès !", Alert.AlertType.INFORMATION);
            } catch (Exception e) {
                showAlert("Erreur", "Erreur lors de la suppression: " + e.getMessage(), Alert.AlertType.ERROR);
            }
        }
    }

    private void voirProjets(Portfolio portfolio) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/ProjetListView.fxml"));
            Parent root = loader.load();
            ProjetListController controller = loader.getController();
            controller.setPortfolio(portfolio);
            Stage stage = (Stage) homeBtn.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle("Path2Learn - Projets de " + portfolio.getTitre());
            stage.show();
        } catch (IOException e) {
            showAlert("Erreur", "Impossible de charger les projets: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    @FXML
    private void handleActualiser() {
        chargerPortfolios();
        searchField.clear();
    }

    private void updateStats() {
        int total = filteredList.size();
        statsLabel.setText(total + " portfolio(s) trouvé(s)");
    }

    public void refreshTable() {
        chargerPortfolios();
    }

    private void showAlert(String title, String message, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}