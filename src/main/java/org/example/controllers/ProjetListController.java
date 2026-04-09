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
import org.example.Models.Projet;
import org.example.Services.ServiceProjet;
import java.io.IOException;
import java.sql.SQLDataException;
import java.util.Optional;

public class ProjetListController {

    @FXML private TableView<Projet> projetTable;
    @FXML private TableColumn<Projet, Integer> colId;
    @FXML private TableColumn<Projet, String> colTitre;
    @FXML private TableColumn<Projet, String> colText;
    @FXML private TableColumn<Projet, String> colDescription;
    @FXML private TableColumn<Projet, String> colTechnologies;
    @FXML private TableColumn<Projet, String> colDateRealisation;
    @FXML private TableColumn<Projet, String> colLienDemo;

    @FXML private Button btnModifier;
    @FXML private Button btnSupprimer;
    @FXML private TextField searchField;
    @FXML private Label statusLabel;
    @FXML private Label pageLabel;
    @FXML private Label portfolioInfoLabel;

    private ServiceProjet serviceProjet;
    private ObservableList<Projet> projetList;
    private ObservableList<Projet> filteredList;
    private Projet selectedProjet;
    private Portfolio currentPortfolio;
    private int currentPage = 0;
    private int itemsPerPage = 10;

    @FXML
    public void initialize() {
        serviceProjet = new ServiceProjet();
        projetList = FXCollections.observableArrayList();
        filteredList = FXCollections.observableArrayList();

        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colTitre.setCellValueFactory(new PropertyValueFactory<>("titreProjet"));
        colText.setCellValueFactory(new PropertyValueFactory<>("text"));
        colDescription.setCellValueFactory(new PropertyValueFactory<>("description"));
        colTechnologies.setCellValueFactory(new PropertyValueFactory<>("technologies"));
        colDateRealisation.setCellValueFactory(new PropertyValueFactory<>("dateRealisation"));
        colLienDemo.setCellValueFactory(new PropertyValueFactory<>("lienDemo"));

        projetTable.getSelectionModel().selectedItemProperty().addListener((obs, oldSelection, newSelection) -> {
            if (newSelection != null) {
                selectedProjet = newSelection;
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

    public void setPortfolio(Portfolio portfolio) {
        this.currentPortfolio = portfolio;
        if (portfolioInfoLabel != null) {
            portfolioInfoLabel.setText("#" + portfolio.getId() + " - " + portfolio.getTitre());
        }
        loadProjets();
    }

    private void loadProjets() {
        try {
            projetList.clear();
            projetList.addAll(serviceProjet.recupererParPortfolio(currentPortfolio.getId()));
            filteredList.setAll(projetList);
            updateTable();
            statusLabel.setText("✅ " + projetList.size() + " projet(s) chargé(s)");
            statusLabel.setStyle("-fx-text-fill: green;");
        } catch (SQLDataException e) {
            showError("Erreur lors du chargement: " + e.getMessage());
        }
    }

    private void updateTable() {
        int start = currentPage * itemsPerPage;
        int end = Math.min(start + itemsPerPage, filteredList.size());
        if (start < filteredList.size()) {
            projetTable.setItems(FXCollections.observableArrayList(filteredList.subList(start, end)));
            int totalPages = (int) Math.ceil((double) filteredList.size() / itemsPerPage);
            pageLabel.setText("Page " + (currentPage + 1) + " sur " + Math.max(1, totalPages));
        } else {
            if (currentPage > 0 && filteredList.size() > 0) {
                currentPage = 0;
                updateTable();
            } else {
                projetTable.setItems(FXCollections.observableArrayList());
                pageLabel.setText("Page 0 sur 0");
            }
        }
    }

    @FXML
    private void handleAjouter(ActionEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/ProjetAddView.fxml"));
            Parent root = loader.load();
            ProjetAddController controller = loader.getController();
            controller.setPortfolioId(currentPortfolio.getId(), currentPortfolio.getTitre());
            Scene scene = projetTable.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            showError("Erreur: " + e.getMessage());
        }
    }

    @FXML
    private void handleModifier(ActionEvent event) {
        if (selectedProjet == null) {
            showError("Veuillez sélectionner un projet");
            return;
        }
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/ProjetEditView.fxml"));
            Parent root = loader.load();
            ProjetEditController controller = loader.getController();
            controller.setProjet(selectedProjet);
            Scene scene = projetTable.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            showError("Erreur: " + e.getMessage());
        }
    }

    @FXML
    private void handleSupprimer(ActionEvent event) {
        if (selectedProjet == null) {
            showError("Veuillez sélectionner un projet");
            return;
        }

        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation de suppression");
        alert.setHeaderText("Supprimer le projet");
        alert.setContentText("Êtes-vous sûr de vouloir supprimer : \n\n" +
                selectedProjet.getTitreProjet() + "\n\nCette action est irréversible !");

        Optional<ButtonType> result = alert.showAndWait();
        if (result.isPresent() && result.get() == ButtonType.OK) {
            try {
                serviceProjet.supprimer(selectedProjet);
                loadProjets();
                showSuccess("Projet supprimé avec succès!");
                selectedProjet = null;
            } catch (SQLDataException e) {
                showError("Erreur lors de la suppression: " + e.getMessage());
            }
        }
    }

    @FXML
    private void handleActualiser(ActionEvent event) {
        loadProjets();
        searchField.clear();
    }

    @FXML
    private void handleRechercher(ActionEvent event) {
        String searchText = searchField.getText().toLowerCase();
        if (searchText.isEmpty()) {
            filteredList.setAll(projetList);
        } else {
            filteredList.setAll(projetList.filtered(p ->
                    p.getTitreProjet().toLowerCase().contains(searchText) ||
                            p.getTechnologies().toLowerCase().contains(searchText)
            ));
        }
        currentPage = 0;
        updateTable();
        statusLabel.setText("🔍 " + filteredList.size() + " résultat(s) trouvé(s)");
    }

    @FXML
    private void handlePrevious(ActionEvent event) {
        if (currentPage > 0) { currentPage--; updateTable(); }
    }

    @FXML
    private void handleNext(ActionEvent event) {
        if ((currentPage + 1) * itemsPerPage < filteredList.size()) { currentPage++; updateTable(); }
    }

    @FXML
    private void handleRetourPortfolios(ActionEvent event) {
        navigateTo("/fxml/PortfolioListView.fxml");
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
            Scene scene = projetTable.getScene();
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