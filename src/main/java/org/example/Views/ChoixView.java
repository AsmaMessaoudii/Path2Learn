package org.example.Views;

import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.geometry.Insets;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.*;
import org.example.Models.Choix;
import org.example.Services.ChoixService;

import java.sql.SQLException;

public class ChoixView {

    private ChoixService service = new ChoixService();
    private TableView<Choix> table = new TableView<>();
    private ObservableList<Choix> data = FXCollections.observableArrayList();

    // Supprimé tfId
    private TextField tfTexte = new TextField();
    private TextField tfQuestionId = new TextField();
    private CheckBox cbCorrect = new CheckBox("Est correct ?");

    public VBox getView() {
        VBox root = new VBox(15);
        root.setPadding(new Insets(20));
        root.setStyle("-fx-background-color: #f0f4f8;");

        Label title = new Label("Gestion des Choix");
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

        tfTexte.setPromptText("Texte du choix (ex: Paris)");
        tfQuestionId.setPromptText("Question ID");

        grid.add(new Label("Texte:"), 0, 0);
        grid.add(tfTexte, 1, 0);
        grid.add(new Label("Question ID:"), 2, 0);
        grid.add(tfQuestionId, 3, 0);
        grid.add(cbCorrect, 0, 1);

        return grid;
    }

    private void buildTable() {
        TableColumn<Choix, Integer> colId = new TableColumn<>("ID");
        TableColumn<Choix, String> colTexte = new TableColumn<>("Texte");
        TableColumn<Choix, Boolean> colCorrect = new TableColumn<>("Correct ?");
        TableColumn<Choix, Integer> colQuestionId = new TableColumn<>("Question ID");

        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colTexte.setCellValueFactory(new PropertyValueFactory<>("contenu")); // Utiliser contenu au lieu de texte
        colTexte.setPrefWidth(200);
        colCorrect.setCellValueFactory(new PropertyValueFactory<>("estCorrect"));
        colQuestionId.setCellValueFactory(new PropertyValueFactory<>("questionId"));

        table.getColumns().addAll(colId, colTexte, colCorrect, colQuestionId);
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
            Choix c = new Choix(
                    tfTexte.getText(),
                    cbCorrect.isSelected(),
                    Integer.parseInt(tfQuestionId.getText())
            );
            service.ajouter(c);
            chargerDonnees();
            clearForm();
            showAlert("Succès", "Choix ajouté avec succès !");
        } catch (Exception e) {
            showAlert("Erreur", e.getMessage());
        }
    }

    private void modifier() {
        Choix selected = table.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert("Information", "Veuillez sélectionner un choix à modifier");
            return;
        }

        try {
            Choix c = new Choix(
                    selected.getId(), // Utiliser l'ID de l'élément sélectionné
                    tfTexte.getText(),
                    cbCorrect.isSelected(),
                    Integer.parseInt(tfQuestionId.getText())
            );
            service.modifier(c);
            chargerDonnees();
            clearForm();
            showAlert("Succès", "Choix modifié avec succès !");
        } catch (Exception e) {
            showAlert("Erreur", e.getMessage());
        }
    }

    private void supprimer() {
        Choix selected = table.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert("Information", "Veuillez sélectionner un choix à supprimer");
            return;
        }

        try {
            service.supprimer(selected);
            chargerDonnees();
            clearForm();
            showAlert("Succès", "Choix supprimé avec succès !");
        } catch (Exception e) {
            showAlert("Erreur", e.getMessage());
        }
    }

    private void remplirFormulaire(Choix c) {
        // Ne pas remplir tfId car il n'existe plus
        tfTexte.setText(c.getContenu());
        cbCorrect.setSelected(c.isEstCorrect());
        tfQuestionId.setText(String.valueOf(c.getQuestionId()));
    }

    private void clearForm() {
        tfTexte.clear();
        tfQuestionId.clear();
        cbCorrect.setSelected(false);
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