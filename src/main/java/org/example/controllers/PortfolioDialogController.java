package org.example.controllers;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.layout.VBox;
import org.example.Models.Portfolio;
import org.example.Services.ServicePortfolio;

import java.util.Date;

public class PortfolioDialogController {

    @FXML private Label dialogTitle;
    @FXML private TextField titreField;
    @FXML private TextArea descriptionArea;
    @FXML private Button saveButton;
    @FXML private Label titreError;
    @FXML private Label descriptionError;

    private ServicePortfolio servicePortfolio;
    private Portfolio portfolio;
    private PortfolioListController parentController;

    @FXML
    public void initialize() {
        titreField.textProperty().addListener((obs, old, newVal) -> validateTitre());
        descriptionArea.textProperty().addListener((obs, old, newVal) -> validateDescription());
    }

    private boolean validateTitre() {
        String titre = titreField.getText();
        if (titre == null || titre.trim().isEmpty()) {
            titreError.setText("Le titre est obligatoire");
            titreField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else if (titre.trim().length() < 3) {
            titreError.setText("Le titre doit contenir au moins 3 caractères");
            titreField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else {
            titreError.setText("");
            titreField.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
            return true;
        }
    }

    private boolean validateDescription() {
        String description = descriptionArea.getText();
        if (description == null || description.trim().isEmpty()) {
            descriptionError.setText("La description est obligatoire");
            descriptionArea.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else if (description.trim().length() < 10) {
            descriptionError.setText("La description doit contenir au moins 10 caractères");
            descriptionArea.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else {
            descriptionError.setText("");
            descriptionArea.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
            return true;
        }
    }

    private boolean validateAllFields() {
        boolean isValid = true;
        isValid &= validateTitre();
        isValid &= validateDescription();
        return isValid;
    }

    public void setServicePortfolio(ServicePortfolio servicePortfolio) {
        this.servicePortfolio = servicePortfolio;
    }

    public void setPortfolio(Portfolio portfolio) {
        this.portfolio = portfolio;
        if (portfolio != null) {
            dialogTitle.setText("Modifier mon portfolio");
            titreField.setText(portfolio.getTitre());
            descriptionArea.setText(portfolio.getDescription());
        }
    }

    public void setParentController(PortfolioListController parentController) {
        this.parentController = parentController;
    }

    @FXML
    private void handleSauvegarder() {
        if (!validateAllFields()) return;

        if (portfolio == null) portfolio = new Portfolio();

        portfolio.setTitre(titreField.getText().trim());
        portfolio.setDescription(descriptionArea.getText().trim());
        portfolio.setUserId(1);
        portfolio.setDateMiseAjour(new Date());
        if (portfolio.getDateCreation() == null) {
            portfolio.setDateCreation(new Date());
        }

        try {
            if (portfolio.getId() == 0) {
                servicePortfolio.ajouter(portfolio);
                showSuccessMessage("Portfolio créé avec succès !");
            } else {
                servicePortfolio.modifier(portfolio);
                showSuccessMessage("Portfolio modifié avec succès !");
            }
            parentController.refreshTable();
            fermerDialog();
        } catch (Exception e) {
            showErrorMessage("Erreur: " + e.getMessage());
        }
    }

    @FXML
    private void handleAnnuler() {
        fermerDialog();
    }

    private void fermerDialog() {
        saveButton.getScene().getWindow().hide();
    }

    private void showSuccessMessage(String message) {
        Label label = new Label("✓ " + message);
        label.setStyle("-fx-text-fill: #4CAF50; -fx-font-size: 12px;");
        VBox parent = (VBox) saveButton.getScene().getRoot();
        parent.getChildren().add(label);
        new Thread(() -> {
            try { Thread.sleep(2000); Platform.runLater(() -> parent.getChildren().remove(label)); }
            catch (InterruptedException e) { e.printStackTrace(); }
        }).start();
    }

    private void showErrorMessage(String message) {
        Label label = new Label("✗ " + message);
        label.setStyle("-fx-text-fill: #f44336; -fx-font-size: 12px;");
        VBox parent = (VBox) saveButton.getScene().getRoot();
        parent.getChildren().add(label);
        new Thread(() -> {
            try { Thread.sleep(3000); Platform.runLater(() -> parent.getChildren().remove(label)); }
            catch (InterruptedException e) { e.printStackTrace(); }
        }).start();
    }
}