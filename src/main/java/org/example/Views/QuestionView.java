package org.example.Views;

import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.geometry.Insets;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.*;
import org.example.Models.Question;
import org.example.Services.QuestionService;

import java.sql.SQLException;

public class QuestionView {

    private QuestionService service = new QuestionService();
    private TableView<Question> table = new TableView<>();
    private ObservableList<Question> data = FXCollections.observableArrayList();

    // Supprimé tfId
    private TextField tfTitre = new TextField();
    private TextField tfDuree = new TextField();
    private TextField tfPoints = new TextField();
    private TextField tfUserId = new TextField();  // Changé de tfCoursId à tfUserId
    private TextArea taDesc = new TextArea();

    public VBox getView() {
        VBox root = new VBox(15);
        root.setPadding(new Insets(20));
        root.setStyle("-fx-background-color: #f0f4f8;");

        Label title = new Label("Gestion des Questions");
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

        tfTitre.setPromptText("Titre de la question");
        tfDuree.setPromptText("Durée (secondes)");
        tfPoints.setPromptText("Points (ex: 20.0)");
        tfUserId.setPromptText("ID du professeur");
        taDesc.setPromptText("Contenu de la question");
        taDesc.setPrefHeight(60);

        grid.add(new Label("Titre:"), 0, 0);
        grid.add(tfTitre, 1, 0);
        grid.add(new Label("Durée:"), 2, 0);
        grid.add(tfDuree, 3, 0);

        grid.add(new Label("Points:"), 0, 1);
        grid.add(tfPoints, 1, 1);
        grid.add(new Label("ID Professeur:"), 2, 1);
        grid.add(tfUserId, 3, 1);

        grid.add(new Label("Description:"), 0, 2);
        GridPane.setColumnSpan(taDesc, 3);
        grid.add(taDesc, 1, 2);

        return grid;
    }

    private void buildTable() {
        TableColumn<Question, Integer> colId = new TableColumn<>("ID");
        TableColumn<Question, String> colTitre = new TableColumn<>("Titre");
        TableColumn<Question, String> colDesc = new TableColumn<>("Description");
        TableColumn<Question, Integer> colDuree = new TableColumn<>("Durée (s)");
        TableColumn<Question, Float> colPoints = new TableColumn<>("Points");
        TableColumn<Question, Integer> colUserId = new TableColumn<>("ID Professeur");  // Changé de Cours ID à ID Professeur

        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colTitre.setCellValueFactory(new PropertyValueFactory<>("titre"));
        colTitre.setPrefWidth(150);
        colDesc.setCellValueFactory(new PropertyValueFactory<>("description"));
        colDesc.setPrefWidth(200);
        colDuree.setCellValueFactory(new PropertyValueFactory<>("duree"));
        colPoints.setCellValueFactory(new PropertyValueFactory<>("noteMax"));
        colUserId.setCellValueFactory(new PropertyValueFactory<>("userId"));  // Utiliser userId

        table.getColumns().addAll(colId, colTitre, colDesc, colDuree, colPoints, colUserId);
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
        // Validation des champs
        if (!validateForm()) return;

        try {
            Question q = new Question(
                    tfTitre.getText(),
                    taDesc.getText(),
                    new java.util.Date(),
                    Integer.parseInt(tfDuree.getText()),
                    Float.parseFloat(tfPoints.getText()),
                    Integer.parseInt(tfUserId.getText())  // Utiliser tfUserId
            );
            service.ajouter(q);
            chargerDonnees();
            clearForm();
            showAlert("Succès", "Question ajoutée avec succès !");
        } catch (NumberFormatException e) {
            showAlert("Erreur", "Veuillez vérifier les nombres (durée, points, ID professeur)");
        } catch (Exception e) {
            showAlert("Erreur", e.getMessage());
        }
    }

    private void modifier() {
        Question selected = table.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert("Information", "Veuillez sélectionner une question à modifier");
            return;
        }

        if (!validateForm()) return;

        try {
            Question q = new Question(
                    selected.getId(),  // Utiliser l'ID de l'élément sélectionné
                    tfTitre.getText(),
                    taDesc.getText(),
                    new java.util.Date(),
                    Integer.parseInt(tfDuree.getText()),
                    Float.parseFloat(tfPoints.getText()),
                    Integer.parseInt(tfUserId.getText())  // Utiliser tfUserId
            );
            service.modifier(q);
            chargerDonnees();
            clearForm();
            showAlert("Succès", "Question modifiée avec succès !");
        } catch (NumberFormatException e) {
            showAlert("Erreur", "Veuillez vérifier les nombres (durée, points, ID professeur)");
        } catch (Exception e) {
            showAlert("Erreur", e.getMessage());
        }
    }

    private void supprimer() {
        Question selected = table.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert("Information", "Veuillez sélectionner une question à supprimer");
            return;
        }

        // Confirmation avant suppression
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Confirmation");
        confirm.setContentText("Voulez-vous vraiment supprimer cette question ?");

        if (confirm.showAndWait().get() == ButtonType.OK) {
            try {
                service.supprimer(selected);
                chargerDonnees();
                clearForm();
                showAlert("Succès", "Question supprimée avec succès !");
            } catch (Exception e) {
                showAlert("Erreur", e.getMessage());
            }
        }
    }

    private void remplirFormulaire(Question q) {
        tfTitre.setText(q.getTitre());
        taDesc.setText(q.getDescription());
        tfDuree.setText(String.valueOf(q.getDuree()));
        tfPoints.setText(String.valueOf(q.getNoteMax()));
        tfUserId.setText(String.valueOf(q.getUserId()));  // Utiliser getUserId()
    }

    private boolean validateForm() {
        if (tfTitre.getText().isEmpty()) {
            showAlert("Validation", "Le titre est obligatoire");
            return false;
        }
        if (tfDuree.getText().isEmpty()) {
            showAlert("Validation", "La durée est obligatoire");
            return false;
        }
        if (tfPoints.getText().isEmpty()) {
            showAlert("Validation", "Les points sont obligatoires");
            return false;
        }
        if (tfUserId.getText().isEmpty()) {
            showAlert("Validation", "L'ID du professeur est obligatoire");
            return false;
        }
        return true;
    }

    private void clearForm() {
        tfTitre.clear();
        taDesc.clear();
        tfDuree.clear();
        tfPoints.clear();
        tfUserId.clear();
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