package org.example.controllers;

import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import org.example.Models.Projet;

public class ProjetDetailsController {

    @FXML private Label labelId;
    @FXML private Label labelTitre;
    @FXML private Label labelText;
    @FXML private Label labelDescription;
    @FXML private Label labelTechnologies;
    @FXML private Label labelDateRealisation;
    @FXML private Label labelLienDemo;
    @FXML private Label labelPortfolioId;
    @FXML private Button closeButton;

    public void setProjet(Projet projet) {
        labelId.setText(String.valueOf(projet.getId()));
        labelTitre.setText(projet.getTitreProjet());
        labelText.setText(projet.getText());
        labelDescription.setText(projet.getDescription());
        labelTechnologies.setText(projet.getTechnologies());
        labelDateRealisation.setText(String.valueOf(projet.getDateRealisation()));
        labelPortfolioId.setText(String.valueOf(projet.getPortfolioId()));

        // Clickable link
        String lien = projet.getLienDemo();
        labelLienDemo.setText(lien);
        labelLienDemo.setStyle("-fx-text-fill: #2196F3; -fx-underline: true; -fx-cursor: hand;");
        labelLienDemo.setOnMouseClicked(e -> {
            try {
                java.awt.Desktop.getDesktop().browse(new java.net.URI(lien));
            } catch (Exception ex) {
                System.out.println("❌ Impossible d'ouvrir le lien: " + ex.getMessage());
            }
        });
    }

    @FXML
    private void handleFermer() {
        closeButton.getScene().getWindow().hide();
    }
}