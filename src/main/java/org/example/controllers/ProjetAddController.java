package org.example.controllers;

import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import org.example.Models.Portfolio;
import org.example.Models.Projet;
import org.example.Services.ServiceProjet;
import java.io.IOException;
import java.sql.SQLDataException;
import java.time.LocalDate;
import java.sql.Date;

public class ProjetAddController {

    @FXML private TextField titreField;
    @FXML private TextField textField;
    @FXML private TextArea descriptionArea;
    @FXML private TextField technologiesField;
    @FXML private DatePicker dateRealisationPicker;
    @FXML private TextField lienDemoField;
    @FXML private Label portfolioInfoLabel;
    @FXML private Label statusLabel;
    @FXML private Label titreErrorLabel;

    private ServiceProjet serviceProjet;
    private int currentPortfolioId;
    private String currentPortfolioTitre;

    @FXML
    public void initialize() {
        serviceProjet = new ServiceProjet();
        titreField.textProperty().addListener((obs, oldVal, newVal) -> validateTitre());
    }

    private void validateTitre() {
        String titre = titreField.getText();
        if (titre == null || titre.trim().isEmpty()) {
            titreErrorLabel.setText("❌ Le titre est obligatoire");
            titreErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        } else if (titre.length() < 3) {
            titreErrorLabel.setText("❌ Minimum 3 caractères");
            titreErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        } else {
            titreErrorLabel.setText("✅ Titre valide");
            titreErrorLabel.setStyle("-fx-text-fill: green; -fx-font-size: 11px;");
        }
    }

    public void setPortfolioId(int portfolioId, String portfolioTitre) {
        this.currentPortfolioId = portfolioId;
        this.currentPortfolioTitre = portfolioTitre;
        if (portfolioInfoLabel != null) {
            portfolioInfoLabel.setText("#" + portfolioId + " - " + portfolioTitre);
        }
    }

    @FXML
    private void handleAjouter(ActionEvent event) {
        validateTitre();

        if (titreField.getText().trim().isEmpty()) {
            showError("Le titre est obligatoire");
            return;
        }
        if (descriptionArea.getText().trim().isEmpty()) {
            showError("La description est obligatoire");
            return;
        }
        if (dateRealisationPicker.getValue() == null) {
            showError("La date de réalisation est obligatoire");
            return;
        }

        try {
            Projet projet = new Projet(
                    titreField.getText().trim(),
                    textField.getText().trim(),
                    descriptionArea.getText().trim(),
                    technologiesField.getText().trim(),
                    Date.valueOf(dateRealisationPicker.getValue()),
                    lienDemoField.getText().trim(),
                    currentPortfolioId
            );

            serviceProjet.ajouter(projet);

            Alert alert = new Alert(Alert.AlertType.INFORMATION);
            alert.setTitle("Succès");
            alert.setHeaderText(null);
            alert.setContentText("Projet ajouté avec succès !");
            alert.showAndWait();

            handleRetour(event);

        } catch (SQLDataException e) {
            showError("Erreur: " + e.getMessage());
        }
    }

    @FXML
    private void handleAnnuler(ActionEvent event) {
        titreField.clear();
        textField.clear();
        descriptionArea.clear();
        technologiesField.clear();
        dateRealisationPicker.setValue(null);
        lienDemoField.clear();
        titreErrorLabel.setText("");
        statusLabel.setText("Formulaire réinitialisé");
        statusLabel.setStyle("-fx-text-fill: blue;");
    }

    @FXML
    private void handleRetour(ActionEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/ProjetListView.fxml"));
            Parent root = loader.load();
            ProjetListController controller = loader.getController();
            Portfolio p = new Portfolio();
            p.setId(currentPortfolioId);
            p.setTitre(currentPortfolioTitre);
            controller.setPortfolio(p);
            Scene scene = titreField.getScene();
            scene.setRoot(root);
        } catch (IOException e) {
            showError("Erreur lors du retour");
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
            Scene scene = titreField.getScene();
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

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}