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
import javafx.stage.Stage;
import org.example.Models.Portfolio;
import org.example.Services.ServicePortfolio;

import java.io.IOException;
import java.util.List;
import java.util.Optional;

public class PortfolioListBackController {

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
    @FXML private ComboBox<String> userFilterCombo;

    // Boutons de navigation
    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;

    private ServicePortfolio servicePortfolio;
    private ObservableList<Portfolio> portfolioList;
    private FilteredList<Portfolio> filteredList;
    private List<Portfolio> allPortfolioList;

    @FXML
    public void initialize() {
        servicePortfolio = new ServicePortfolio();
        setupTableColumns();
        chargerPortfolios();
        setupSearchFilter();
        setupUserFilter();
        setupViewButton();

        // Mettre en surbrillance le bouton actif
        setActiveButton(projetsBtn);
    }

    // ==================== MÉTHODES DE NAVIGATION ====================

    @FXML
    private void handleHome() {
        naviguerVers("/fxml/MainMenu.fxml", "Path2Learn - Accueil");
    }

    @FXML
    private void handleCours() {
        naviguerVers("/fxml/CoursView.fxml", "Path2Learn - Gestion des cours");
    }

    @FXML
    private void handleRessources() {
        naviguerVers("/fxml/RessourcesView.fxml", "Path2Learn - Gestion des ressources");
    }

    @FXML
    private void handleQuestions() {
        naviguerVers("/fxml/QuestionListView.fxml", "Path2Learn - Quiz");
    }

    @FXML
    private void handleProjets() {
        // Déjà sur la page des portfolios
        chargerPortfolios();
        setActiveButton(projetsBtn);
    }

    @FXML
    private void handleEvenements() {
        showAlert("Événements", "Module Événements - Bientôt disponible", Alert.AlertType.INFORMATION);
        setActiveButton(evenementsBtn);
    }

    @FXML
    private void handleUtilisateurs() {
        naviguerVers("/fxml/User.fxml", "Path2Learn - Utilisateurs");
    }

    private void naviguerVers(String fxmlPath, String titre) {
        try {
            java.net.URL resource = getClass().getResource(fxmlPath);
            if (resource == null) {
                showAlert("Erreur", "Page non trouvée: " + fxmlPath, Alert.AlertType.ERROR);
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
            showAlert("Erreur", "Impossible de charger la page: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void setActiveButton(Button activeBtn) {
        String defaultStyle = "-fx-background-color: transparent; -fx-text-fill: #A0A0A0; -fx-font-size: 14px; -fx-cursor: hand; -fx-padding: 12 20; -fx-alignment: CENTER_LEFT;";
        String activeStyle = "-fx-background-color: #E8F5E9; -fx-text-fill: #81C784; -fx-font-size: 14px; -fx-font-weight: bold; -fx-cursor: hand; -fx-padding: 12 20; -fx-alignment: CENTER_LEFT; -fx-border-color: transparent #81C784 transparent transparent; -fx-border-width: 0 4 0 0;";

        homeBtn.setStyle(defaultStyle);
        coursBtn.setStyle(defaultStyle);
        ressourcesBtn.setStyle(defaultStyle);
        questionsBtn.setStyle(defaultStyle);
        projetsBtn.setStyle(defaultStyle);
        evenementsBtn.setStyle(defaultStyle);
        utilisateursBtn.setStyle(defaultStyle);

        activeBtn.setStyle(activeStyle);
    }

    // ==================== MÉTHODES D'AFFICHAGE ====================

    private void setupTableColumns() {
        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colTitre.setCellValueFactory(new PropertyValueFactory<>("titre"));

        colDescription.setCellValueFactory(new PropertyValueFactory<>("description"));
        colDescription.setCellFactory(col -> new TableCell<Portfolio, String>() {
            @Override
            protected void updateItem(String item, boolean empty) {
                super.updateItem(item, empty);
                if (empty || item == null) {
                    setText(null);
                } else {
                    String text = item.length() > 100 ? item.substring(0, 100) + "..." : item;
                    setText(text);
                }
            }
        });

        colDateCreation.setCellValueFactory(new PropertyValueFactory<>("dateCreation"));
        colDateMiseAjour.setCellValueFactory(new PropertyValueFactory<>("dateMiseAjour"));
        colUserId.setCellValueFactory(new PropertyValueFactory<>("userId"));
    }

    private void chargerPortfolios() {
        try {
            allPortfolioList = servicePortfolio.recuperer();
            portfolioList = FXCollections.observableArrayList(allPortfolioList);
            filteredList = new FilteredList<>(portfolioList, p -> true);

            setupUserFilterCombo();

            SortedList<Portfolio> sortedList = new SortedList<>(filteredList);
            sortedList.comparatorProperty().bind(tableView.comparatorProperty());
            tableView.setItems(sortedList);
            updateStats();
        } catch (Exception e) {
            showAlert("Erreur", "Impossible de charger les portfolios: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void setupUserFilterCombo() {
        userFilterCombo.getItems().clear();
        userFilterCombo.getItems().add("Tous les utilisateurs");
        if (allPortfolioList != null) {
            allPortfolioList.stream()
                    .map(Portfolio::getUserId)
                    .distinct()
                    .sorted()
                    .forEach(userId -> userFilterCombo.getItems().add("Utilisateur " + userId));
        }
        userFilterCombo.setValue("Tous les utilisateurs");
    }

    private void setupSearchFilter() {
        searchField.textProperty().addListener((observable, oldValue, newValue) -> {
            filteredList.setPredicate(portfolio -> {
                if (newValue == null || newValue.isEmpty()) {
                    return true;
                }
                String lowerCaseFilter = newValue.toLowerCase();
                return portfolio.getTitre().toLowerCase().contains(lowerCaseFilter) ||
                        portfolio.getDescription().toLowerCase().contains(lowerCaseFilter);
            });
            updateStats();
        });
    }

    private void setupUserFilter() {
        userFilterCombo.valueProperty().addListener((observable, oldValue, newValue) -> {
            if (newValue == null || newValue.equals("Tous les utilisateurs")) {
                filteredList.setPredicate(portfolio -> true);
            } else {
                int userId = Integer.parseInt(newValue.replace("Utilisateur ", ""));
                filteredList.setPredicate(portfolio -> portfolio.getUserId() == userId);
            }
            updateStats();
        });
    }

    private void setupViewButton() {
        colActions.setCellFactory(col -> new TableCell<Portfolio, Void>() {
            private final Button viewBtn = new Button("📁 Voir projets");
            private final HBox buttons = new HBox(8, viewBtn);

            {
                viewBtn.setStyle("-fx-background-color: #81C784; -fx-text-fill: white; -fx-font-size: 12px; -fx-padding: 6 12; -fx-background-radius: 5; -fx-cursor: hand;");
                buttons.setAlignment(Pos.CENTER);
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                if (empty) {
                    setGraphic(null);
                } else {
                    Portfolio portfolio = getTableView().getItems().get(getIndex());
                    viewBtn.setOnAction(e -> voirProjets(portfolio));
                    setGraphic(buttons);
                }
            }
        });
    }

    private void voirProjets(Portfolio portfolio) {
        try {
            // CHANGED: Now points to ProjetListViewBack.fxml instead of ProjetListView.fxml
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/ProjetListViewBack.fxml"));
            Parent root = loader.load();

            // CHANGED: Now uses ProjetListBackController instead of ProjetListController
            ProjetListBackController projetController = loader.getController();
            projetController.setPortfolio(portfolio);

            Stage stage = (Stage) homeBtn.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle("Path2Learn - Projets du portfolio: " + portfolio.getTitre());
            stage.show();

        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger les projets: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    @FXML
    private void handleActualiser() {
        chargerPortfolios();
        searchField.clear();
        userFilterCombo.setValue("Tous les utilisateurs");
        showAlert("Info", "Liste des portfolios actualisée !", Alert.AlertType.INFORMATION);
    }

    @FXML
    private void handleRechercher() {
        updateStats();
    }

    @FXML
    private void handleResetRecherche() {
        searchField.clear();
        userFilterCombo.setValue("Tous les utilisateurs");
        chargerPortfolios();
    }

    private void updateStats() {
        int total = filteredList.size();
        statsLabel.setText(total + " portfolio" + (total > 1 ? "s" : "") + " trouvé" + (total > 1 ? "s" : ""));
    }

    private void showAlert(String title, String message, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}