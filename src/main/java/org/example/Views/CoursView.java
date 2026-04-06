package org.example.Views;

import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.geometry.Insets;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.*;
import org.example.Models.Cours;
import org.example.Services.ServiceCours;

import java.sql.Date;
import java.sql.SQLException;

public class CoursView {

    private ServiceCours service = new ServiceCours();
    private TableView<Cours> table = new TableView<>();
    private ObservableList<Cours> data = FXCollections.observableArrayList();

    // Supprimer tfId - l'ID sera auto-généré
    private TextField tfTitre = new TextField();
    private TextField tfMatiere = new TextField();
    private ComboBox<String> cbNiveau = new ComboBox<>();
    private TextField tfDuree = new TextField();
    private TextField tfEmail = new TextField();
    private ComboBox<String> cbStatut = new ComboBox<>();
    private TextField tfUserId = new TextField();
    private TextArea taDesc = new TextArea();
    private DatePicker dpDate = new DatePicker();

    public VBox getView() {
        VBox root = new VBox(15);
        root.setPadding(new Insets(20));
        root.getStyleClass().add("vbox-main");

        Label title = new Label("📚 Gestion des Cours");
        title.getStyleClass().add("label-title");

        GridPane form = buildForm();

        HBox buttons = new HBox(10);
        Button btnAjouter = new Button("➕ Ajouter");
        Button btnModifier = new Button("✏️ Modifier");
        Button btnSupprimer = new Button("🗑️ Supprimer");
        Button btnActualiser = new Button("🔄 Actualiser");

        btnAjouter.getStyleClass().add("button-primary");
        btnModifier.getStyleClass().add("button-warning");
        btnSupprimer.getStyleClass().add("button-danger");
        btnActualiser.getStyleClass().add("button-info");

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
        grid.getStyleClass().add("grid-form");

        // Configuration des ComboBox
        cbNiveau.getItems().addAll("Débutant", "Intermédiaire", "Avancé");
        cbNiveau.setPromptText("Sélectionnez le niveau");

        cbStatut.getItems().addAll("actif", "inactif", "publié");
        cbStatut.setPromptText("Sélectionnez le statut");

        tfTitre.setPromptText("Entrez le titre du cours");
        tfMatiere.setPromptText("Entrez la matière");
        tfDuree.setPromptText("Durée (en heures)");
        tfEmail.setPromptText("email@exemple.com");
        tfUserId.setPromptText("ID de l'utilisateur");
        taDesc.setPromptText("Description détaillée du cours");
        taDesc.setPrefHeight(80);
        dpDate.setPromptText("Sélectionnez la date");

        grid.add(new Label("Titre:"), 0, 0);
        grid.add(tfTitre, 1, 0);
        grid.add(new Label("Matière:"), 2, 0);
        grid.add(tfMatiere, 3, 0);

        grid.add(new Label("Niveau:"), 0, 1);
        grid.add(cbNiveau, 1, 1);
        grid.add(new Label("Durée (h):"), 2, 1);
        grid.add(tfDuree, 3, 1);

        grid.add(new Label("Email Prof:"), 0, 2);
        grid.add(tfEmail, 1, 2);
        grid.add(new Label("Statut:"), 2, 2);
        grid.add(cbStatut, 3, 2);

        grid.add(new Label("User ID:"), 0, 3);
        grid.add(tfUserId, 1, 3);
        grid.add(new Label("Date création:"), 2, 3);
        grid.add(dpDate, 3, 3);

        grid.add(new Label("Description:"), 0, 4);
        GridPane.setColumnSpan(taDesc, 3);
        grid.add(taDesc, 1, 4);

        // Rendre certains champs obligatoires avec un style
        tfTitre.setStyle("-fx-border-color: #4a90e2;");

        return grid;
    }

    private void buildTable() {
        TableColumn<Cours, Integer> colId = new TableColumn<>("ID");
        TableColumn<Cours, String> colTitre = new TableColumn<>("Titre");
        TableColumn<Cours, String> colNiveau = new TableColumn<>("Niveau");
        TableColumn<Cours, String> colMat = new TableColumn<>("Matière");
        TableColumn<Cours, Integer> colDuree = new TableColumn<>("Durée (h)");
        TableColumn<Cours, String> colEmail = new TableColumn<>("Email Professeur");
        TableColumn<Cours, String> colStatut = new TableColumn<>("Statut");

        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colTitre.setCellValueFactory(new PropertyValueFactory<>("titre"));
        colTitre.setPrefWidth(180);
        colNiveau.setCellValueFactory(new PropertyValueFactory<>("niveau"));
        colMat.setCellValueFactory(new PropertyValueFactory<>("matiere"));
        colDuree.setCellValueFactory(new PropertyValueFactory<>("duree"));
        colEmail.setCellValueFactory(new PropertyValueFactory<>("email_prof"));
        colEmail.setPrefWidth(180);
        colStatut.setCellValueFactory(new PropertyValueFactory<>("statut"));

        table.getColumns().addAll(colId, colTitre, colNiveau, colMat, colDuree, colEmail, colStatut);
        table.setItems(data);
        table.setPrefHeight(300);
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
        if (!validateForm()) return;

        try {
            Cours c = new Cours(
                    tfTitre.getText(),
                    taDesc.getText(),
                    cbNiveau.getValue(),
                    tfMatiere.getText(),
                    Integer.parseInt(tfDuree.getText()),
                    Date.valueOf(dpDate.getValue()),
                    tfEmail.getText(),
                    cbStatut.getValue(),
                    Integer.parseInt(tfUserId.getText())
            );
            service.ajouter(c);
            chargerDonnees();
            clearForm();
            showAlert("Succès", "✅ Cours ajouté avec succès !");
        } catch (Exception e) {
            showAlert("Erreur", "❌ " + e.getMessage());
        }
    }

    private void modifier() {
        Cours selected = table.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert("Information", "Veuillez sélectionner un cours à modifier");
            return;
        }

        if (!validateForm()) return;

        try {
            Cours c = new Cours(
                    tfTitre.getText(),
                    taDesc.getText(),
                    cbNiveau.getValue(),
                    tfMatiere.getText(),
                    Integer.parseInt(tfDuree.getText()),
                    Date.valueOf(dpDate.getValue()),
                    tfEmail.getText(),
                    cbStatut.getValue(),
                    Integer.parseInt(tfUserId.getText())
            );
            c.setId(selected.getId());
            service.modifier(c);
            chargerDonnees();
            clearForm();
            showAlert("Succès", "✅ Cours modifié avec succès !");
        } catch (Exception e) {
            showAlert("Erreur", "❌ " + e.getMessage());
        }
    }

    private void supprimer() {
        Cours selected = table.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert("Information", "Veuillez sélectionner un cours à supprimer");
            return;
        }

        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Confirmation");
        confirm.setContentText("Voulez-vous vraiment supprimer ce cours ?");

        if (confirm.showAndWait().get() == ButtonType.OK) {
            try {
                service.supprimer(selected);
                chargerDonnees();
                clearForm();
                showAlert("Succès", "✅ Cours supprimé avec succès !");
            } catch (Exception e) {
                showAlert("Erreur", "❌ " + e.getMessage());
            }
        }
    }

    private void remplirFormulaire(Cours c) {
        // Pas de tfId à remplir
        tfTitre.setText(c.getTitre());
        tfMatiere.setText(c.getMatiere());
        cbNiveau.setValue(c.getNiveau());
        tfDuree.setText(String.valueOf(c.getDuree()));
        tfEmail.setText(c.getEmail_prof());
        cbStatut.setValue(c.getStatut());
        tfUserId.setText(String.valueOf(c.getUser_id()));
        taDesc.setText(c.getDescription());
        if (c.getDate_creation() != null) {
            dpDate.setValue(c.getDate_creation().toLocalDate());
        }
    }

    private boolean validateForm() {
        if (tfTitre.getText().isEmpty()) {
            showAlert("Validation", "Le titre est obligatoire");
            return false;
        }
        if (cbNiveau.getValue() == null) {
            showAlert("Validation", "Veuillez sélectionner un niveau");
            return false;
        }
        if (tfDuree.getText().isEmpty()) {
            showAlert("Validation", "La durée est obligatoire");
            return false;
        }
        if (dpDate.getValue() == null) {
            showAlert("Validation", "Veuillez sélectionner une date");
            return false;
        }
        return true;
    }

    private void clearForm() {
        tfTitre.clear();
        tfMatiere.clear();
        cbNiveau.setValue(null);
        tfDuree.clear();
        tfEmail.clear();
        cbStatut.setValue(null);
        tfUserId.clear();
        taDesc.clear();
        dpDate.setValue(null);
    }

    private void showAlert(String title, String msg) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(msg);
        alert.showAndWait();
    }
}