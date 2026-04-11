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
import java.sql.Date;

public class ProjetEditController {

    @FXML private TextField titreField;
    @FXML private TextField textField;
    @FXML private TextArea descriptionArea;
    @FXML private TextField technologiesField;
    @FXML private DatePicker dateRealisationPicker;
    @FXML private TextField lienDemoField;
    @FXML private Label projetIdLabel;
    @FXML private Label statusLabel;

    private ServiceProjet serviceProjet;
    private Projet currentProjet;

    @FXML
    public void initialize() {
        serviceProjet = new ServiceProjet();
    }

    public void setProjet(Projet projet) {
        this.currentProjet = projet;
        projetIdLabel.setText("Modification du projet #" + projet.getId());
        titreField.setText(projet.getTitreProjet());
        textField.setText(projet.getText());
        descriptionArea.setText(projet.getDescription());
        technologiesField.setText(projet.getTechnologies());
        if (projet.getDateRealisation() != null) {
            dateRealisationPicker.setValue(((Date) projet.getDateRealisation()).toLocalDate());
        }
        lienDemoField.setText(projet.getLienDemo());
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
        if (dateRealisationPicker.getValue() == null) {
            showError("La date de réalisation est obligatoire");
            return;
        }

        try {
            currentProjet.setTitreProjet(titreField.getText().trim());
            currentProjet.setText(textField.getText().trim());
            currentProjet.setDescription(descriptionArea.getText().trim());
            currentProjet.setTechnologies(technologiesField.getText().trim());
            currentProjet.setDateRealisation(Date.valueOf(dateRealisationPicker.getValue()));
            currentProjet.setLienDemo(lienDemoField.getText().trim());

            serviceProjet.modifier(currentProjet);

            Alert alert = new Alert(Alert.AlertType.INFORMATION);
            alert.setTitle("Succès");
            alert.setHeaderText(null);
            alert.setContentText("Projet modifié avec succès !");
            alert.showAndWait();

            handleRetour(event);

        } catch (SQLDataException e) {
            showError("Erreur: " + e.getMessage());
        }
    }

    @FXML
    private void handleAnnuler(ActionEvent event) {
        setProjet(currentProjet);
        statusLabel.setText("Modifications annulées");
    }

    @FXML
    private void handleRetour(ActionEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/ProjetListView.fxml"));
            Parent root = loader.load();
            ProjetListController controller = loader.getController();
            Portfolio p = new Portfolio();
            p.setId(currentProjet.getPortfolioId());
            p.setTitre("Portfolio #" + currentProjet.getPortfolioId());
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