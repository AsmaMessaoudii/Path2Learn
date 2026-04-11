package org.example.controllers;

import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import org.example.Models.Portfolio;
import org.example.Services.ServicePortfolio;
import java.io.IOException;
import java.sql.SQLDataException;
import java.util.Date;

public class PortfolioAddController {

    @FXML private TextField titreField;
    @FXML private TextArea descriptionArea;
    @FXML private TextField userIdField;
    @FXML private Label statusLabel;
    @FXML private Label titreErrorLabel;

    private ServicePortfolio servicePortfolio;

    @FXML
    public void initialize() {
        servicePortfolio = new ServicePortfolio();

        titreField.textProperty().addListener((obs, oldVal, newVal) -> validateTitre());
    }

    private void validateTitre() {
        String titre = titreField.getText();
        if (titre == null || titre.trim().isEmpty()) {
            titreErrorLabel.setText("❌ Le titre est obligatoire");
            titreErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        } else if (titre.length() < 3) {
            titreErrorLabel.setText("❌ Le titre doit contenir au moins 3 caractères");
            titreErrorLabel.setStyle("-fx-text-fill: red; -fx-font-size: 11px;");
        } else {
            titreErrorLabel.setText("✅ Titre valide");
            titreErrorLabel.setStyle("-fx-text-fill: green; -fx-font-size: 11px;");
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

        try {
            int userId = Integer.parseInt(userIdField.getText().trim());

            Portfolio portfolio = new Portfolio(
                    titreField.getText().trim(),
                    descriptionArea.getText().trim(),
                    new Date(),
                    new Date(),
                    userId
            );

            servicePortfolio.ajouter(portfolio);

            Alert alert = new Alert(Alert.AlertType.INFORMATION);
            alert.setTitle("Succès");
            alert.setHeaderText(null);
            alert.setContentText("Portfolio ajouté avec succès !");
            alert.showAndWait();

            handleRetour(event);

        } catch (NumberFormatException e) {
            showError("User ID invalide");
        } catch (SQLDataException e) {
            showError("Erreur: " + e.getMessage());
        }
    }

    @FXML
    private void handleAnnuler(ActionEvent event) {
        titreField.clear();
        descriptionArea.clear();
        userIdField.clear();
        titreErrorLabel.setText("");
        statusLabel.setText("Formulaire réinitialisé");
        statusLabel.setStyle("-fx-text-fill: blue;");
    }

    @FXML
    private void handleRetour(ActionEvent event) {
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