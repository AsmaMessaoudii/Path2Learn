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
import org.example.Models.Portfolio;
import org.example.Services.ServicePortfolio;
import java.io.IOException;
import java.sql.SQLDataException;
import java.util.Optional;

public class PortfolioListController {

    @FXML private TableView<Portfolio> portfolioTable;
    @FXML private TableColumn<Portfolio, Integer> colId;
    @FXML private TableColumn<Portfolio, String> colTitre;
    @FXML private TableColumn<Portfolio, String> colDescription;
    @FXML private TableColumn<Portfolio, String> colDateCreation;
    @FXML private TableColumn<Portfolio, String> colDateMiseAjour;
    @FXML private TableColumn<Portfolio, Integer> colUserId;

    @FXML private Button btnModifier;
    @FXML private Button btnSupprimer;
    @FXML private Button btnVoirProjets;
    @FXML private TextField searchField;
    @FXML private Label statusLabel;
    @FXML private Label pageLabel;

    private ServicePortfolio servicePortfolio;
    private ObservableList<Portfolio> portfolioList;
    private ObservableList<Portfolio> filteredList;
    private Portfolio selectedPortfolio;
    private int currentPage = 0;
    private int itemsPerPage = 10;

    @FXML
    public void initialize() {
        servicePortfolio = new ServicePortfolio();
        portfolioList = FXCollections.observableArrayList();
        filteredList = FXCollections.observableArrayList();

        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colTitre.setCellValueFactory(new PropertyValueFactory<>("titre"));
        colDescription.setCellValueFactory(new PropertyValueFactory<>("description"));
        colDateCreation.setCellValueFactory(new PropertyValueFactory<>("dateCreation"));
        colDateMiseAjour.setCellValueFactory(new PropertyValueFactory<>("dateMiseAjour"));
        colUserId.setCellValueFactory(new PropertyValueFactory<>("userId"));

        portfolioTable.getSelectionModel().selectedItemProperty().addListener((obs, oldSelection, newSelection) -> {
            if (newSelection != null) {
                selectedPortfolio = newSelection;
                btnModifier.setDisable(false);
                btnSupprimer.setDisable(false);
                btnVoirProjets.setDisable(false);
            } else {
                btnModifier.setDisable(true);
                btnSupprimer.setDisable(true);
                btnVoirProjets.setDisable(true);
            }
        });

        btnModifier.setDisable(true);
        btnSupprimer.setDisable(true);
        btnVoirProjets.setDisable(true);

        loadPortfolios();
    }

    private void loadPortfolios() {
        try {
            portfolioList.clear();
            portfolioList.addAll(servicePortfolio.recuperer());
            filteredList.setAll(portfolioList);
            updateTable();
            statusLabel.setText("✅ " + portfolioList.size() + " portfolio(s) chargé(s)");
            statusLabel.setStyle("-fx-text-fill: green;");
        } catch (SQLDataException e) {
            showError("Erreur lors du chargement: " + e.getMessage());
        }
    }

    private void updateTable() {
        int start = currentPage * itemsPerPage;
        int end = Math.min(start + itemsPerPage, filteredList.size());
        if (start < filteredList.size()) {
            portfolioTable.setItems(FXCollections.observableArrayList(filteredList.subList(start, end)));
            int totalPages = (int) Math.ceil((double) filteredList.size() / itemsPerPage);
            pageLabel.setText("Page " + (currentPage + 1) + " sur " + Math.max(1, totalPages));
        } else {
            if (currentPage > 0 && filteredList.size() > 0) {
                currentPage = 0;
                updateTable();
            } else {
                portfolioTable.setItems(FXCollections.observableArrayList());
                pageLabel.setText("Page 0 sur 0");
            }
        }
    }

    @FXML
    private void handleAjouter(ActionEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/PortfolioAddView.fxml"));
            Parent root = loader.load();
            Scene scene = portfolioTable.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            showError("Erreur: " + e.getMessage());
        }
    }

    @FXML
    private void handleModifier(ActionEvent event) {
        if (selectedPortfolio == null) {
            showError("Veuillez sélectionner un portfolio");
            return;
        }
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/PortfolioEditView.fxml"));
            Parent root = loader.load();
            PortfolioEditController controller = loader.getController();
            controller.setPortfolio(selectedPortfolio);
            Scene scene = portfolioTable.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            showError("Erreur: " + e.getMessage());
        }
    }

    @FXML
    private void handleSupprimer(ActionEvent event) {
        if (selectedPortfolio == null) {
            showError("Veuillez sélectionner un portfolio");
            return;
        }

        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation de suppression");
        alert.setHeaderText("Supprimer le portfolio");
        alert.setContentText("Êtes-vous sûr de vouloir supprimer : \n\n" +
                selectedPortfolio.getTitre() + "\n\nCette action est irréversible !");

        Optional<ButtonType> result = alert.showAndWait();
        if (result.isPresent() && result.get() == ButtonType.OK) {
            try {
                servicePortfolio.supprimer(selectedPortfolio);
                loadPortfolios();
                showSuccess("Portfolio supprimé avec succès!");
                selectedPortfolio = null;
            } catch (SQLDataException e) {
                showError("Erreur lors de la suppression: " + e.getMessage());
            }
        }
    }

    @FXML
    private void handleVoirProjets(ActionEvent event) {
        if (selectedPortfolio == null) {
            showError("Veuillez sélectionner un portfolio");
            return;
        }
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/ProjetListView.fxml"));
            Parent root = loader.load();
            ProjetListController controller = loader.getController();
            controller.setPortfolio(selectedPortfolio);
            Scene scene = portfolioTable.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            showError("Erreur: " + e.getMessage());
        }
    }

    @FXML
    private void handleActualiser(ActionEvent event) {
        loadPortfolios();
        searchField.clear();
    }

    @FXML
    private void handleRechercher(ActionEvent event) {
        String searchText = searchField.getText().toLowerCase();
        if (searchText.isEmpty()) {
            filteredList.setAll(portfolioList);
        } else {
            filteredList.setAll(portfolioList.filtered(p ->
                    p.getTitre().toLowerCase().contains(searchText) ||
                            p.getDescription().toLowerCase().contains(searchText)
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

    @FXML private void handleHome() { navigateTo("/fxml/MainMenu.fxml"); }
    @FXML private void handleCours() { showAlert("Cours", "Page en construction"); }
    @FXML private void handleRessources() { showAlert("Ressources", "Page en construction"); }
    @FXML private void handleQuestions() { navigateTo("/fxml/QuestionListView.fxml"); }
    @FXML private void handleProjets() { navigateTo("/fxml/PortfolioListView.fxml"); }
    @FXML private void handleEvenements() { showAlert("Événements", "Page en construction"); }
    @FXML private void handleUtilisateurs() { showAlert("Communauté", "Page en construction"); }

    private void navigateTo(String fxmlPath) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();
            Scene scene = portfolioTable.getScene();
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