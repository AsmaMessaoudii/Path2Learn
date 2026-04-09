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

public class PortfolioEditController {

    @FXML private TextField titreField;
    @FXML private TextArea descriptionArea;
    @FXML private TextField userIdField;
    @FXML private Label portfolioIdLabel;
    @FXML private Label statusLabel;

    private ServicePortfolio servicePortfolio;
    private Portfolio currentPortfolio;

    @FXML
    public void initialize() {
        servicePortfolio = new ServicePortfolio();
    }

    public void setPortfolio(Portfolio portfolio) {
        this.currentPortfolio = portfolio;
        portfolioIdLabel.setText("Modification du portfolio #" + portfolio.getId());
        titreField.setText(portfolio.getTitre());
        descriptionArea.setText(portfolio.getDescription());
        userIdField.setText(String.valueOf(portfolio.getUserId()));
    }

    @FXML
    private void handleModifier(ActionEvent event) {
        if (titreField.getText().trim().isEmpty()) {
            showError("Le titre est obligatoire");
            return;
        }
        if (descriptionArea.getText().trim().isEmpty()) {
            showError("La description est obligatoire");
            return;
        }

        try {
            currentPortfolio.setTitre(titreField.getText().trim());
            currentPortfolio.setDescription(descriptionArea.getText().trim());
            currentPortfolio.setDateMiseAjour(new Date());
            currentPortfolio.setUserId(Integer.parseInt(userIdField.getText().trim()));

            servicePortfolio.modifier(currentPortfolio);

            Alert alert = new Alert(Alert.AlertType.INFORMATION);
            alert.setTitle("Succès");
            alert.setHeaderText(null);
            alert.setContentText("Portfolio modifié avec succès !");
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
        setPortfolio(currentPortfolio);
        statusLabel.setText("Modifications annulées");
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