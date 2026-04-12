package org.example.controllers;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.layout.VBox;
import org.example.Models.Projet;
import org.example.Services.ServiceProjet;

import java.sql.Date;

public class ProjetDialogController {

    @FXML private Label dialogTitle;
    @FXML private TextField titreField;
    @FXML private TextField textField;
    @FXML private TextArea descriptionArea;
    @FXML private TextField technologiesField;
    @FXML private DatePicker dateRealisationPicker;
    @FXML private TextField lienDemoField;
    @FXML private Button saveButton;

    @FXML private Label titreError;
    @FXML private Label descriptionError;
    @FXML private Label dateError;
    @FXML private Label lienDemoError;

    private ServiceProjet serviceProjet;
    private Projet projet;
    private int currentPortfolioId;
    private ProjetListController parentController;

    @FXML
    public void initialize() {
        titreField.textProperty().addListener((obs, old, newVal) -> validateTitre());
        descriptionArea.textProperty().addListener((obs, old, newVal) -> validateDescription());
        dateRealisationPicker.valueProperty().addListener((obs, old, newVal) -> validateDate());
        lienDemoField.textProperty().addListener((obs, old, newVal) -> validateLienDemo());
    }

    // ==================== VALIDATIONS ====================

    private boolean validateTitre() {
        String titre = titreField.getText();
        if (titre == null || titre.trim().isEmpty()) {
            titreError.setText("❌ Le titre est obligatoire");
            titreField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else if (titre.trim().length() < 3) {
            titreError.setText("❌ Le titre doit contenir au moins 3 caractères");
            titreField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else {
            titreError.setText("✅ Titre valide");
            titreError.setStyle("-fx-text-fill: #4CAF50; -fx-font-size: 11px;");
            titreField.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
            return true;
        }
    }

    private boolean validateDescription() {
        String description = descriptionArea.getText();
        if (description == null || description.trim().isEmpty()) {
            descriptionError.setText("❌ La description est obligatoire");
            descriptionError.setStyle("-fx-text-fill: #f44336; -fx-font-size: 11px;");
            descriptionArea.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else if (description.trim().length() < 10) {
            descriptionError.setText("❌ La description doit contenir au moins 10 caractères");
            descriptionError.setStyle("-fx-text-fill: #f44336; -fx-font-size: 11px;");
            descriptionArea.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else {
            descriptionError.setText("✅ Description valide");
            descriptionError.setStyle("-fx-text-fill: #4CAF50; -fx-font-size: 11px;");
            descriptionArea.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
            return true;
        }
    }

    private boolean validateDate() {
        if (dateRealisationPicker.getValue() == null) {
            dateError.setText("❌ La date est obligatoire");
            dateError.setStyle("-fx-text-fill: #f44336; -fx-font-size: 11px;");
            dateRealisationPicker.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else {
            dateError.setText("✅ Date valide");
            dateError.setStyle("-fx-text-fill: #4CAF50; -fx-font-size: 11px;");
            dateRealisationPicker.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
            return true;
        }
    }

    private boolean validateLienDemo() {
        String lien = lienDemoField.getText();
        if (lien == null || lien.trim().isEmpty()) {
            lienDemoError.setText("");
            lienDemoField.setStyle("-fx-border-color: #E0E0E0; -fx-border-radius: 8;");
            return true;
        } else if (!lien.trim().startsWith("https://")) {
            lienDemoError.setText("❌ Le lien doit commencer par https://");
            lienDemoError.setStyle("-fx-text-fill: #f44336; -fx-font-size: 11px;");
            lienDemoField.setStyle("-fx-border-color: #f44336; -fx-border-radius: 8;");
            return false;
        } else {
            lienDemoError.setText("✅ Lien valide");
            lienDemoError.setStyle("-fx-text-fill: #4CAF50; -fx-font-size: 11px;");
            lienDemoField.setStyle("-fx-border-color: #4CAF50; -fx-border-radius: 8;");
            return true;
        }
    }

    private boolean validateAllFields() {
        boolean isValid = true;
        isValid &= validateTitre();
        isValid &= validateDescription();
        isValid &= validateDate();
        isValid &= validateLienDemo();
        return isValid;
    }

    // ==================== SETTERS ====================

    public void setServiceProjet(ServiceProjet serviceProjet) {
        this.serviceProjet = serviceProjet;
    }

    public void setPortfolioId(int portfolioId) {
        this.currentPortfolioId = portfolioId;
    }

    public void setProjet(Projet projet) {
        this.projet = projet;
        if (projet != null) {
            dialogTitle.setText("Modifier le projet");
            titreField.setText(projet.getTitreProjet());
            textField.setText(projet.getText());
            descriptionArea.setText(projet.getDescription());
            technologiesField.setText(projet.getTechnologies());
            if (projet.getDateRealisation() != null) {
                dateRealisationPicker.setValue(
                        ((Date) projet.getDateRealisation()).toLocalDate()
                );
            }
            lienDemoField.setText(projet.getLienDemo());
        }
    }

    public void setParentController(ProjetListController parentController) {
        this.parentController = parentController;
    }

    // ==================== ACTIONS ====================

    @FXML
    private void handleSauvegarder() {
        if (!validateAllFields()) return;

        if (projet == null) projet = new Projet();

        projet.setTitreProjet(titreField.getText().trim());
        projet.setText(textField.getText().trim());
        projet.setDescription(descriptionArea.getText().trim());
        projet.setTechnologies(technologiesField.getText().trim());
        projet.setDateRealisation(Date.valueOf(dateRealisationPicker.getValue()));
        projet.setLienDemo(lienDemoField.getText().trim());
        projet.setPortfolioId(currentPortfolioId);

        try {
            if (projet.getId() == 0) {
                serviceProjet.ajouter(projet);
                showSuccessMessage("Projet ajouté avec succès !");
            } else {
                serviceProjet.modifier(projet);
                showSuccessMessage("Projet modifié avec succès !");
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
            try {
                Thread.sleep(2000);
                Platform.runLater(() -> parent.getChildren().remove(label));
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
        }).start();
    }

    private void showErrorMessage(String message) {
        Label label = new Label("✗ " + message);
        label.setStyle("-fx-text-fill: #f44336; -fx-font-size: 12px;");
        VBox parent = (VBox) saveButton.getScene().getRoot();
        parent.getChildren().add(label);
        new Thread(() -> {
            try {
                Thread.sleep(3000);
                Platform.runLater(() -> parent.getChildren().remove(label));
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
        }).start();
    }
}