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
import org.example.Services.ServicePortfolio;
import org.example.Services.ServiceProjet;

import java.io.IOException;
import java.util.List;
import java.util.Optional;

public class PortfolioListController {

    @FXML private VBox mainContent;
    @FXML private Button homeBtn;
    @FXML private Button btnAjouter;

    private ServicePortfolio servicePortfolio;
    private ServiceProjet serviceProjet;
    private Portfolio currentPortfolio;

    @FXML
    public void initialize() {
        servicePortfolio = new ServicePortfolio();
        serviceProjet = new ServiceProjet();
        chargerPortfolio();
    }

    // ==================== NAVIGATION ====================

    @FXML private void handleHome() { naviguerVers("/fxml/HomePage.fxml", "Path2Learn - Accueil"); }
    @FXML private void handleCours() { naviguerVers("/fxml/CoursListFrontView.fxml", "Path2Learn - Cours"); }
    @FXML private void handleRessources() { naviguerVers("/fxml/RessourcesViewFront.fxml", "Path2Learn - Ressources"); }
    @FXML private void handleQuestions() { naviguerVers("/fxml/QuizList.fxml", "Path2Learn - Quiz"); }
    @FXML private void handleProjets() { chargerPortfolio(); }
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

    // ==================== LOAD ====================

    private void chargerPortfolio() {
        mainContent.getChildren().clear();
        try {
            List<Portfolio> list = servicePortfolio.recuperer();

            if (list.isEmpty()) {
                afficherEtatVide();
            } else {
                currentPortfolio = list.get(0);
                afficherCartePortfolio(currentPortfolio);
                // hide add button since portfolio exists
                btnAjouter.setVisible(false);
                btnAjouter.setManaged(false);
            }
        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void afficherEtatVide() {
        btnAjouter.setVisible(true);
        btnAjouter.setManaged(true);

        VBox emptyState = new VBox(15);
        emptyState.setAlignment(javafx.geometry.Pos.CENTER);
        emptyState.setStyle("-fx-padding: 80 0;");

        Label icon = new Label("📂");
        icon.setStyle("-fx-font-size: 60px;");

        Label msg = new Label("Vous n'avez pas encore de portfolio");
        msg.setStyle("-fx-font-size: 20px; -fx-font-weight: bold; -fx-text-fill: #888888;");

        Label sub = new Label("Créez votre portfolio pour commencer à ajouter vos projets !");
        sub.setStyle("-fx-font-size: 14px; -fx-text-fill: #AAAAAA;");

        emptyState.getChildren().addAll(icon, msg, sub);
        mainContent.getChildren().add(emptyState);
    }

    private void afficherCartePortfolio(Portfolio portfolio) {
        // Portfolio card
        VBox card = new VBox(15);
        card.setStyle(
                "-fx-background-color: white;" +
                        "-fx-background-radius: 15;" +
                        "-fx-padding: 30;" +
                        "-fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.08), 15, 0, 0, 3);"
        );

        // Top row: title + badges
        HBox topRow = new HBox(15);
        topRow.setAlignment(javafx.geometry.Pos.CENTER_LEFT);

        Label icon = new Label("💼");
        icon.setStyle("-fx-font-size: 36px;");

        VBox titleBlock = new VBox(4);
        Label titre = new Label(portfolio.getTitre());
        titre.setStyle("-fx-font-size: 22px; -fx-font-weight: bold; -fx-text-fill: #2E5C2E;");

        Label dateCreation = new Label("📅 Créé le : " + portfolio.getDateCreation());
        dateCreation.setStyle("-fx-font-size: 12px; -fx-text-fill: #999999;");

        Label dateMaj = new Label("🔄 Mis à jour : " + portfolio.getDateMiseAjour());
        dateMaj.setStyle("-fx-font-size: 12px; -fx-text-fill: #999999;");

        titleBlock.getChildren().addAll(titre, dateCreation, dateMaj);
        topRow.getChildren().addAll(icon, titleBlock);

        // Description
        Label description = new Label(portfolio.getDescription());
        description.setStyle("-fx-font-size: 14px; -fx-text-fill: #555555; -fx-wrap-text: true;");
        description.setMaxWidth(Double.MAX_VALUE);

        // Separator
        Separator sep = new Separator();

        // Action buttons
        HBox actions = new HBox(12);
        actions.setAlignment(javafx.geometry.Pos.CENTER_LEFT);

        Button btnModifier = new Button("✏️ Modifier le portfolio");
        btnModifier.setStyle(
                "-fx-background-color: #2196F3; -fx-text-fill: white;" +
                        "-fx-font-weight: bold; -fx-padding: 10 20;" +
                        "-fx-background-radius: 8; -fx-cursor: hand;"
        );
        btnModifier.setOnAction(e -> ouvrirDialog(portfolio));

        Button btnSupprimer = new Button("🗑️ Supprimer");
        btnSupprimer.setStyle(
                "-fx-background-color: #f44336; -fx-text-fill: white;" +
                        "-fx-font-weight: bold; -fx-padding: 10 20;" +
                        "-fx-background-radius: 8; -fx-cursor: hand;"
        );
        btnSupprimer.setOnAction(e -> handleSupprimer(portfolio));

        Button btnProjets = new Button("📁 Voir mes projets");
        btnProjets.setStyle(
                "-fx-background-color: #81C784; -fx-text-fill: white;" +
                        "-fx-font-weight: bold; -fx-padding: 10 20;" +
                        "-fx-background-radius: 8; -fx-cursor: hand;"
        );
        btnProjets.setOnAction(e -> voirProjets(portfolio));

        actions.getChildren().addAll(btnModifier, btnSupprimer, btnProjets);

        card.getChildren().addAll(topRow, description, sep, actions);
        mainContent.getChildren().add(card);
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
            dialogStage.setTitle(portfolio == null ? "Créer mon portfolio" : "Modifier mon portfolio");
            dialogStage.setScene(new Scene(dialogVBox));
            dialogStage.showAndWait();

        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible d'ouvrir le dialogue: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void handleSupprimer(Portfolio portfolio) {
        // First, check if there are any projects linked to this portfolio
        try {
            List<Projet> projets = serviceProjet.recupererParPortfolio(portfolio.getId());

            String message;
            if (projets != null && !projets.isEmpty()) {
                message = "Voulez-vous vraiment supprimer \"" + portfolio.getTitre() + "\" ?\n\n"
                        + "⚠️ ATTENTION : Ce portfolio contient " + projets.size() + " projet(s).\n"
                        + "Tous les projets associés seront également supprimés !";
            } else {
                message = "Voulez-vous vraiment supprimer \"" + portfolio.getTitre() + "\" ?";
            }

            Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
            alert.setTitle("Confirmation de suppression");
            alert.setHeaderText("Supprimer le portfolio");
            alert.setContentText(message);

            Optional<ButtonType> result = alert.showAndWait();
            if (result.isPresent() && result.get() == ButtonType.OK) {
                try {
                    // First delete all linked projects
                    if (projets != null && !projets.isEmpty()) {
                        for (Projet projet : projets) {
                            try {
                                serviceProjet.supprimer(projet);
                            } catch (Exception e) {
                                System.err.println("Error deleting project " + projet.getId() + ": " + e.getMessage());
                            }
                        }
                    }

                    // Then delete the portfolio
                    servicePortfolio.supprimer(portfolio);

                    showAlert("Succès", "Portfolio et ses projets supprimés avec succès !", Alert.AlertType.INFORMATION);
                    chargerPortfolio(); // Refresh the view

                } catch (Exception e) {
                    e.printStackTrace();
                    showAlert("Erreur", "Erreur lors de la suppression: " + e.getMessage(), Alert.AlertType.ERROR);
                }
            }
        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Erreur", "Erreur lors de la vérification des projets: " + e.getMessage(), Alert.AlertType.ERROR);
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

    public void refreshTable() {
        chargerPortfolio();
    }

    private void showAlert(String title, String message, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}