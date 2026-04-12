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
import org.example.Models.Projet;
import org.example.Services.ServiceProjet;

import java.io.IOException;
import java.util.Optional;

public class ProjetListController {

    @FXML private TableView<Projet> tableView;
    @FXML private TableColumn<Projet, Integer> colId;
    @FXML private TableColumn<Projet, String> colTitre;
    @FXML private TableColumn<Projet, String> colText;
    @FXML private TableColumn<Projet, String> colDescription;
    @FXML private TableColumn<Projet, String> colTechnologies;
    @FXML private TableColumn<Projet, String> colDateRealisation;
    @FXML private TableColumn<Projet, String> colLienDemo;
    @FXML private TableColumn<Projet, Void> colActions;

    @FXML private TextField searchField;
    @FXML private Label statsLabel;
    @FXML private Label portfolioInfoLabel;
    @FXML private Button homeBtn;

    private ServiceProjet serviceProjet;
    private ObservableList<Projet> projetList;
    private FilteredList<Projet> filteredList;
    private Portfolio currentPortfolio;

    @FXML
    public void initialize() {
        serviceProjet = new ServiceProjet();
        setupTableColumns();
        setupActionButtons();
    }

    // ==================== NAVIGATION ====================
    @FXML private void handleHome() { naviguerVers("/fxml/HomePage.fxml", "Path2Learn - Accueil"); }
    @FXML private void handleCours() { naviguerVers("/fxml/CoursListFrontView.fxml", "Path2Learn - Cours"); }
    @FXML private void handleRessources() { naviguerVers("/fxml/RessourcesViewFront.fxml", "Path2Learn - Ressources"); }
    @FXML private void handleQuestions() { naviguerVers("/fxml/QuizList.fxml", "Path2Learn - Quiz"); }
    @FXML private void handleProjets() { naviguerVers("/fxml/PortfolioListView.fxml", "Path2Learn - Portfolios"); }
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
        colTitre.setCellValueFactory(new PropertyValueFactory<>("titreProjet"));
        colText.setCellValueFactory(new PropertyValueFactory<>("text"));

        colDescription.setCellValueFactory(new PropertyValueFactory<>("description"));
        colDescription.setCellFactory(col -> new TableCell<Projet, String>() {
            @Override
            protected void updateItem(String item, boolean empty) {
                super.updateItem(item, empty);
                if (empty || item == null) setText(null);
                else setText(item.length() > 80 ? item.substring(0, 80) + "..." : item);
            }
        });

        colTechnologies.setCellValueFactory(new PropertyValueFactory<>("technologies"));
        colDateRealisation.setCellValueFactory(new PropertyValueFactory<>("dateRealisation"));
        colLienDemo.setCellValueFactory(new PropertyValueFactory<>("lienDemo"));
    }

    public void setPortfolio(Portfolio portfolio) {
        this.currentPortfolio = portfolio;
        if (portfolioInfoLabel != null) {
            portfolioInfoLabel.setText("#" + portfolio.getId() + " - " + portfolio.getTitre());
        }
        chargerProjets();
        setupSearchFilter();
        setupActionButtons();
    }

    private void chargerProjets() {
        try {
            projetList = FXCollections.observableArrayList(
                    serviceProjet.recupererParPortfolio(currentPortfolio.getId())
            );
            filteredList = new FilteredList<>(projetList, p -> true);
            SortedList<Projet> sortedList = new SortedList<>(filteredList);
            sortedList.comparatorProperty().bind(tableView.comparatorProperty());
            tableView.setItems(sortedList);
            updateStats();
        } catch (Exception e) {
            showAlert("Erreur", "Impossible de charger les projets: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void setupSearchFilter() {
        searchField.textProperty().addListener((observable, oldValue, newValue) -> {
            filteredList.setPredicate(projet -> {
                if (newValue == null || newValue.isEmpty()) return true;
                String lower = newValue.toLowerCase();
                return projet.getTitreProjet().toLowerCase().contains(lower) ||
                        projet.getTechnologies().toLowerCase().contains(lower) ||
                        projet.getDescription().toLowerCase().contains(lower);
            });
            updateStats();
        });
    }

    private void setupActionButtons() {
        colActions.setCellFactory(col -> new TableCell<Projet, Void>() {
            private final Button editBtn = new Button("✏️ Modifier");
            private final Button deleteBtn = new Button("🗑️ Supprimer");
            private final Button detailsBtn = new Button("👁️ Détails");
            private final HBox buttons = new HBox(8, editBtn, deleteBtn, detailsBtn);

            {
                editBtn.setStyle("-fx-background-color: #2196F3; -fx-text-fill: white; -fx-font-size: 11px; -fx-padding: 5 10; -fx-background-radius: 5; -fx-cursor: hand;");
                deleteBtn.setStyle("-fx-background-color: #f44336; -fx-text-fill: white; -fx-font-size: 11px; -fx-padding: 5 10; -fx-background-radius: 5; -fx-cursor: hand;");
                detailsBtn.setStyle("-fx-background-color: #9C27B0; -fx-text-fill: white; -fx-font-size: 11px; -fx-padding: 5 10; -fx-background-radius: 5; -fx-cursor: hand;");
                buttons.setAlignment(Pos.CENTER);
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                if (empty) {
                    setGraphic(null);
                } else {
                    Projet projet = getTableView().getItems().get(getIndex());
                    editBtn.setOnAction(e -> ouvrirDialog(projet));
                    deleteBtn.setOnAction(e -> handleSupprimer(projet));
                    detailsBtn.setOnAction(e -> voirDetails(projet));
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

    private void ouvrirDialog(Projet projet) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/ProjetDialog.fxml"));
            VBox dialogVBox = loader.load();

            ProjetDialogController dialogController = loader.getController();
            dialogController.setServiceProjet(serviceProjet);
            dialogController.setProjet(projet);
            dialogController.setPortfolioId(currentPortfolio.getId());
            dialogController.setParentController(this);

            Stage dialogStage = new Stage();
            dialogStage.initModality(Modality.APPLICATION_MODAL);
            dialogStage.setTitle(projet == null ? "Ajouter un projet" : "Modifier un projet");
            dialogStage.setScene(new Scene(dialogVBox));
            dialogStage.showAndWait();

        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible d'ouvrir le dialogue: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void handleSupprimer(Projet projet) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation");
        alert.setHeaderText("Supprimer le projet");
        alert.setContentText("Voulez-vous vraiment supprimer : " + projet.getTitreProjet() + " ?");

        Optional<ButtonType> result = alert.showAndWait();
        if (result.isPresent() && result.get() == ButtonType.OK) {
            try {
                serviceProjet.supprimer(projet);
                chargerProjets();
                showAlert("Succès", "Projet supprimé avec succès !", Alert.AlertType.INFORMATION);
            } catch (Exception e) {
                showAlert("Erreur", "Erreur lors de la suppression: " + e.getMessage(), Alert.AlertType.ERROR);
            }
        }
    }

    @FXML
    private void handleRetourPortfolios() {
        naviguerVers("/fxml/PortfolioListView.fxml", "Path2Learn - Portfolios");
    }

    @FXML
    private void handleActualiser() {
        chargerProjets();
        searchField.clear();
    }

    private void updateStats() {
        int total = filteredList.size();
        statsLabel.setText(total + " projet(s) trouvé(s)");
    }

    public void refreshTable() {
        chargerProjets();
    }

    private void showAlert(String title, String message, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void voirDetails(Projet projet) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/ProjetDetailsDialog.fxml"));
            VBox dialogVBox = loader.load();

            ProjetDetailsController controller = loader.getController();
            controller.setProjet(projet);

            Stage dialogStage = new Stage();
            dialogStage.initModality(Modality.APPLICATION_MODAL);
            dialogStage.setTitle("Détails du projet");
            dialogStage.setScene(new Scene(dialogVBox));
            dialogStage.showAndWait();

        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible d'ouvrir les détails: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }
}