package org.example.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.stage.Modality;
import javafx.stage.Stage;
import org.example.Models.Portfolio;
import org.example.Models.Projet;
import org.example.Services.ServiceProjet;

import java.io.IOException;
import java.util.List;
import java.util.Optional;

public class ProjetListController {

    @FXML private VBox mainContent;
    @FXML private Button homeBtn;
    @FXML private Button btnAjouter;
    @FXML private Label portfolioInfoLabel;

    private ServiceProjet serviceProjet;
    private Portfolio currentPortfolio;

    @FXML
    public void initialize() {
        serviceProjet = new ServiceProjet();
    }

    // ==================== NAVIGATION ====================

    @FXML private void handleHome() { naviguerVers("/fxml/HomePage.fxml", "Path2Learn - Accueil"); }
    @FXML private void handleCours() { naviguerVers("/fxml/CoursView.fxml", "Path2Learn - Cours"); }
    @FXML private void handleRessources() { naviguerVers("/fxml/RessourcesView.fxml", "Path2Learn - Ressources"); }
    @FXML private void handleQuestions() { naviguerVers("/fxml/QuestionListView.fxml", "Path2Learn - Quiz"); }
    @FXML private void handleProjets() { naviguerVers("/fxml/PortfolioListView.fxml", "Path2Learn - Portfolio"); }
    @FXML private void handleEvenements() { showAlert("Événements", "Bientôt disponible", Alert.AlertType.INFORMATION); }
    @FXML private void handleUtilisateurs() { naviguerVers("/fxml/User.fxml", "Path2Learn - Utilisateurs"); }

    @FXML
    private void handleRetourPortfolios() {
        naviguerVers("/fxml/PortfolioListView.fxml", "Path2Learn - Portfolio");
    }

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

    // ==================== LOAD ====================

    public void setPortfolio(Portfolio portfolio) {
        this.currentPortfolio = portfolio;
        if (portfolioInfoLabel != null) {
            portfolioInfoLabel.setText(portfolio.getTitre());
        }
        chargerProjets();
    }

    private void chargerProjets() {
        mainContent.getChildren().clear();
        try {
            List<Projet> list = serviceProjet.recupererParPortfolio(currentPortfolio.getId());

            if (list.isEmpty()) {
                afficherEtatVide();
            } else {
                btnAjouter.setVisible(true);
                btnAjouter.setManaged(true);
                afficherGalerie(list);
            }
        } catch (Exception e) {
            showAlert("Erreur", "Impossible de charger les projets: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void afficherEtatVide() {
        btnAjouter.setVisible(true);
        btnAjouter.setManaged(true);

        VBox emptyState = new VBox(15);
        emptyState.setAlignment(javafx.geometry.Pos.CENTER);
        emptyState.setStyle("-fx-padding: 80 0;");

        Label icon = new Label("🚀");
        icon.setStyle("-fx-font-size: 60px;");

        Label msg = new Label("Aucun projet dans ce portfolio");
        msg.setStyle("-fx-font-size: 20px; -fx-font-weight: bold; -fx-text-fill: #888888;");

        Label sub = new Label("Commencez par ajouter votre premier projet !");
        sub.setStyle("-fx-font-size: 14px; -fx-text-fill: #AAAAAA;");

        emptyState.getChildren().addAll(icon, msg, sub);
        mainContent.getChildren().add(emptyState);
    }

    private void afficherGalerie(List<Projet> projets) {
        // Wrap in a FlowPane for gallery grid
        javafx.scene.layout.FlowPane gallery = new javafx.scene.layout.FlowPane();
        gallery.setHgap(20);
        gallery.setVgap(20);
        gallery.setPrefWrapLength(900);

        for (Projet projet : projets) {
            gallery.getChildren().add(creerCarteProjet(projet));
        }

        mainContent.getChildren().add(gallery);
    }

    private VBox creerCarteProjet(Projet projet) {
        VBox card = new VBox(12);
        card.setPrefWidth(280);
        card.setStyle(
                "-fx-background-color: white;" +
                        "-fx-background-radius: 15;" +
                        "-fx-padding: 20;" +
                        "-fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.08), 15, 0, 0, 3);"
        );

        // Top color band
        Label band = new Label();
        band.setMaxWidth(Double.MAX_VALUE);
        band.setPrefHeight(6);
        band.setStyle("-fx-background-color: #81C784; -fx-background-radius: 5;");

        // Title
        Label titre = new Label(projet.getTitreProjet());
        titre.setStyle("-fx-font-size: 16px; -fx-font-weight: bold; -fx-text-fill: #2E5C2E; -fx-wrap-text: true;");
        titre.setMaxWidth(240);

        // Description preview
        String descPreview = projet.getDescription() != null && projet.getDescription().length() > 80
                ? projet.getDescription().substring(0, 80) + "..."
                : projet.getDescription();
        Label description = new Label(descPreview);
        description.setStyle("-fx-font-size: 12px; -fx-text-fill: #777777; -fx-wrap-text: true;");
        description.setMaxWidth(240);

        // Technologies badge
        HBox techBox = new HBox(5);
        techBox.setAlignment(javafx.geometry.Pos.CENTER_LEFT);
        Label techIcon = new Label("🛠️");
        Label tech = new Label(projet.getTechnologies() != null ? projet.getTechnologies() : "N/A");
        tech.setStyle(
                "-fx-font-size: 11px; -fx-text-fill: white;" +
                        "-fx-background-color: #81C784;" +
                        "-fx-background-radius: 10; -fx-padding: 3 8;"
        );
        techBox.getChildren().addAll(techIcon, tech);

        // Date
        Label date = new Label("📅 " + projet.getDateRealisation());
        date.setStyle("-fx-font-size: 11px; -fx-text-fill: #999999;");

        // Separator
        Separator sep = new Separator();

        // Buttons
        HBox btnRow = new HBox(8);
        btnRow.setAlignment(javafx.geometry.Pos.CENTER);

        Button detailsBtn = new Button("👁️ Détails");
        detailsBtn.setStyle(
                "-fx-background-color: #9C27B0; -fx-text-fill: white;" +
                        "-fx-font-size: 11px; -fx-padding: 6 12;" +
                        "-fx-background-radius: 8; -fx-cursor: hand;"
        );
        detailsBtn.setOnAction(e -> voirDetails(projet));

        Button editBtn = new Button("✏️ Modifier");
        editBtn.setStyle(
                "-fx-background-color: #2196F3; -fx-text-fill: white;" +
                        "-fx-font-size: 11px; -fx-padding: 6 12;" +
                        "-fx-background-radius: 8; -fx-cursor: hand;"
        );
        editBtn.setOnAction(e -> ouvrirDialog(projet));

        Button deleteBtn = new Button("🗑️");
        deleteBtn.setStyle(
                "-fx-background-color: #f44336; -fx-text-fill: white;" +
                        "-fx-font-size: 11px; -fx-padding: 6 10;" +
                        "-fx-background-radius: 8; -fx-cursor: hand;"
        );
        deleteBtn.setOnAction(e -> handleSupprimer(projet));

        btnRow.getChildren().addAll(detailsBtn, editBtn, deleteBtn);

        card.getChildren().addAll(band, titre, description, techBox, date, sep, btnRow);

        // Hover effect
        card.setOnMouseEntered(e -> card.setStyle(
                "-fx-background-color: white;" +
                        "-fx-background-radius: 15;" +
                        "-fx-padding: 20;" +
                        "-fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.15), 20, 0, 0, 5);" +
                        "-fx-scale-x: 1.02; -fx-scale-y: 1.02;"
        ));
        card.setOnMouseExited(e -> card.setStyle(
                "-fx-background-color: white;" +
                        "-fx-background-radius: 15;" +
                        "-fx-padding: 20;" +
                        "-fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.08), 15, 0, 0, 3);"
        ));

        return card;
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
        alert.setContentText("Voulez-vous vraiment supprimer \"" + projet.getTitreProjet() + "\" ?");

        Optional<ButtonType> result = alert.showAndWait();
        if (result.isPresent() && result.get() == ButtonType.OK) {
            try {
                serviceProjet.supprimer(projet);
                chargerProjets();
            } catch (Exception e) {
                showAlert("Erreur", "Erreur lors de la suppression: " + e.getMessage(), Alert.AlertType.ERROR);
            }
        }
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
}