package org.example.Views;

import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.geometry.Insets;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.*;
import org.example.Models.RessourcePedagogique;
import org.example.Services.ServiceRessourcePedagogique;

import java.sql.Date;
import java.sql.SQLException;
import java.sql.Timestamp;

public class RessourceView {

    private ServiceRessourcePedagogique service = new ServiceRessourcePedagogique();
    private TableView<RessourcePedagogique> table = new TableView<>();
    private ObservableList<RessourcePedagogique> data = FXCollections.observableArrayList();

    // Supprimé tfId
    private TextField tfTitre = new TextField();
    private TextField tfType = new TextField();
    private TextField tfUrl = new TextField();
    private TextField tfCoursId = new TextField();
    private TextField tfFileName = new TextField();
    private DatePicker dpDate = new DatePicker();

    public VBox getView() {
        VBox root = new VBox(15);
        root.setPadding(new Insets(20));
        root.setStyle("-fx-background-color: #f0f4f8;");

        Label title = new Label("Gestion des Ressources Pédagogiques");
        title.setStyle("-fx-font-size: 22px; -fx-font-weight: bold; -fx-text-fill: #2c3e50;");

        GridPane form = buildForm();

        HBox buttons = new HBox(10);
        Button btnAjouter = new Button("Ajouter");
        Button btnModifier = new Button("Modifier");
        Button btnSupprimer = new Button("Supprimer");
        Button btnActualiser = new Button("Actualiser");

        styleButton(btnAjouter, "#27ae60");
        styleButton(btnModifier, "#2980b9");
        styleButton(btnSupprimer, "#e74c3c");
        styleButton(btnActualiser, "#7f8c8d");

        buttons.getChildren().addAll(btnAjouter, btnModifier, btnSupprimer, btnActualiser);

        buildTable();

        btnAjouter.setOnAction(e -> ajouter());
        btnModifier.setOnAction(e -> modifier());
        btnSupprimer.setOnAction(e -> supprimer());
        btnActualiser.setOnAction(e -> chargerDonnees());

        table.getSelectionModel().selectedItemProperty().addListener(
                (obs, old, selected) -> {
                    if (selected != null) remplirFormulaire(selected);
                }
        );

        chargerDonnees();
        root.getChildren().addAll(title, form, buttons, table);
        return root;
    }

    private GridPane buildForm() {
        GridPane grid = new GridPane();
        grid.setHgap(10);
        grid.setVgap(10);
        grid.setPadding(new Insets(15));
        grid.setStyle("-fx-background-color: white; -fx-background-radius: 8;");

        tfTitre.setPromptText("Titre");
        tfType.setPromptText("pdf / video / image / lien");
        tfUrl.setPromptText("http://...");
        tfCoursId.setPromptText("Cours ID");
        tfFileName.setPromptText("fichier.pdf");

        grid.add(new Label("Titre:"), 0, 0);
        grid.add(tfTitre, 1, 0);
        grid.add(new Label("Type:"), 2, 0);
        grid.add(tfType, 3, 0);
        grid.add(new Label("Cours ID:"), 0, 1);
        grid.add(tfCoursId, 1, 1);
        grid.add(new Label("URL:"), 2, 1);
        GridPane.setColumnSpan(tfUrl, 2);
        grid.add(tfUrl, 3, 1);
        grid.add(new Label("Nom fichier:"), 0, 2);
        grid.add(tfFileName, 1, 2);
        grid.add(new Label("Date ajout:"), 2, 2);
        grid.add(dpDate, 3, 2);

        return grid;
    }

    private void buildTable() {
        TableColumn<RessourcePedagogique, Integer> colId = new TableColumn<>("ID");
        TableColumn<RessourcePedagogique, String> colTitre = new TableColumn<>("Titre");
        TableColumn<RessourcePedagogique, String> colType = new TableColumn<>("Type");
        TableColumn<RessourcePedagogique, String> colUrl = new TableColumn<>("URL");
        TableColumn<RessourcePedagogique, Integer> colCoursId = new TableColumn<>("Cours ID");
        TableColumn<RessourcePedagogique, String> colFile = new TableColumn<>("Fichier");

        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colTitre.setCellValueFactory(new PropertyValueFactory<>("titre"));
        colTitre.setPrefWidth(140);
        colType.setCellValueFactory(new PropertyValueFactory<>("type"));
        colUrl.setCellValueFactory(new PropertyValueFactory<>("url"));
        colUrl.setPrefWidth(180);
        colCoursId.setCellValueFactory(new PropertyValueFactory<>("cours_id"));
        colFile.setCellValueFactory(new PropertyValueFactory<>("file_name"));

        table.getColumns().addAll(colId, colTitre, colType, colUrl, colCoursId, colFile);
        table.setItems(data);
        table.setPrefHeight(250);
    }

    private void chargerDonnees() {
        try {
            data.clear();
            data.addAll(service.recuperer());
        } catch (SQLException e) {
            showAlert("Erreur", e.getMessage());
        }
    }

    private void ajouter() {
        try {
            RessourcePedagogique r = new RessourcePedagogique(
                    tfTitre.getText(),
                    tfType.getText(),
                    tfUrl.getText(),
                    Date.valueOf(dpDate.getValue()),
                    Integer.parseInt(tfCoursId.getText()),
                    tfFileName.getText(),
                    Timestamp.valueOf(dpDate.getValue().atStartOfDay())
            );
            service.ajouter(r);
            chargerDonnees();
            clearForm();
            showAlert("Succès", "Ressource ajoutée avec succès !");
        } catch (Exception e) {
            showAlert("Erreur", e.getMessage());
        }
    }

    private void modifier() {
        RessourcePedagogique selected = table.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert("Information", "Veuillez sélectionner une ressource à modifier");
            return;
        }

        try {
            RessourcePedagogique r = new RessourcePedagogique(
                    tfTitre.getText(),
                    tfType.getText(),
                    tfUrl.getText(),
                    Date.valueOf(dpDate.getValue()),
                    Integer.parseInt(tfCoursId.getText()),
                    tfFileName.getText(),
                    Timestamp.valueOf(dpDate.getValue().atStartOfDay())
            );
            r.setId(selected.getId());
            service.modifier(r);
            chargerDonnees();
            clearForm();
            showAlert("Succès", "Ressource modifiée avec succès !");
        } catch (Exception e) {
            showAlert("Erreur", e.getMessage());
        }
    }

    private void supprimer() {
        RessourcePedagogique selected = table.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert("Information", "Veuillez sélectionner une ressource à supprimer");
            return;
        }

        try {
            service.supprimer(selected);
            chargerDonnees();
            clearForm();
            showAlert("Succès", "Ressource supprimée avec succès !");
        } catch (Exception e) {
            showAlert("Erreur", e.getMessage());
        }
    }

    private void remplirFormulaire(RessourcePedagogique r) {
        // Ne pas remplir tfId car il n'existe plus
        tfTitre.setText(r.getTitre());
        tfType.setText(r.getType());
        tfUrl.setText(r.getUrl());
        tfCoursId.setText(String.valueOf(r.getCours_id()));
        tfFileName.setText(r.getFile_name());
        if (r.getDate_ajout() != null) {
            dpDate.setValue(r.getDate_ajout().toLocalDate());
        }
    }

    private void clearForm() {
        tfTitre.clear();
        tfType.clear();
        tfUrl.clear();
        tfCoursId.clear();
        tfFileName.clear();
        dpDate.setValue(null);
    }

    private void styleButton(Button btn, String color) {
        btn.setStyle("-fx-background-color: " + color + "; -fx-text-fill: white; " +
                "-fx-font-weight: bold; -fx-background-radius: 5; -fx-padding: 8 16;");
    }

    private void showAlert(String title, String msg) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(msg);
        alert.showAndWait();
    }
}