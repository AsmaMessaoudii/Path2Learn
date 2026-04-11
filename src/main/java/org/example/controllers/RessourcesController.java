package org.example.controllers;

import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.collections.transformation.FilteredList;
import javafx.collections.transformation.SortedList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;
import javafx.stage.Modality;
import javafx.stage.Stage;
import org.example.Models.Cours;
import org.example.Models.RessourcePedagogique;
import org.example.Services.ServiceCours;
import org.example.Services.ServiceRessourcePedagogique;

import java.awt.Desktop;
import java.io.File;
import java.io.IOException;
import java.net.URI;
import java.net.URISyntaxException;
import java.sql.Date;
import java.util.List;
import java.util.Optional;

public class RessourcesController {

    @FXML private TableView<RessourcePedagogique> tableView;
    @FXML private TableColumn<RessourcePedagogique, Integer> colId;
    @FXML private TableColumn<RessourcePedagogique, String> colTitre;
    @FXML private TableColumn<RessourcePedagogique, String> colType;
    @FXML private TableColumn<RessourcePedagogique, String> colUrl;
    @FXML private TableColumn<RessourcePedagogique, String> colFileName;
    @FXML private TableColumn<RessourcePedagogique, Date> colDateAjout;
    @FXML private TableColumn<RessourcePedagogique, Void> colActions;
    @FXML private TableColumn<RessourcePedagogique, String> colCours;

    @FXML private TextField searchField;
    @FXML private Label statsLabel;
    @FXML private ComboBox<String> coursFilterCombo;

    // Boutons de navigation
    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;

    private ServiceRessourcePedagogique serviceRessource;
    private ServiceCours serviceCours;
    private ObservableList<RessourcePedagogique> ressourcesList;
    private FilteredList<RessourcePedagogique> filteredList;
    private List<Cours> coursList;
    private int coursIdFiltre = -1;
    private String coursNomFiltre = null;

    @FXML
    public void initialize() {
        serviceRessource = new ServiceRessourcePedagogique();
        serviceCours = new ServiceCours();
        chargerCours();
        setupTableColumns();
        chargerRessources();
        setupSearchFilter();
        setupCoursFilter();
        setupActionButtons();
    }

    // ==================== MÉTHODES DE NAVIGATION CORRIGÉES ====================

    @FXML
    private void handleHome() {
        naviguerVers("/fxml/MainMenu.fxml");
    }

    @FXML
    private void handleCours() {
        naviguerVers("/fxml/CoursView.fxml");
    }

    @FXML
    private void handleRessources() {
        coursIdFiltre = -1;
        coursNomFiltre = null;
        chargerRessources();
        coursFilterCombo.setDisable(false);
        coursFilterCombo.setValue("Tous les cours");
    }

    @FXML
    private void handleQuestions() {
        naviguerVers("/fxml/QuestionListView.fxml");
    }

    @FXML
    private void handleProjets() {
        naviguerVers("/fxml/PortfolioListView.fxml");
    }

    @FXML
    private void handleEvenements() {
        showInfo("Événements", "Module en construction");
    }

    @FXML
    private void handleUtilisateurs() {
        naviguerVers("/fxml/User.fxml");
    }

    private void naviguerVers(String fxmlPath) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();
            Stage stage = (Stage) homeBtn.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger la page: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void showInfo(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    public void setCoursIdFiltre(int coursId, String coursTitre) {
        this.coursIdFiltre = coursId;
        this.coursNomFiltre = coursTitre;

        if (coursList != null && coursFilterCombo != null) {
            coursFilterCombo.setValue(coursTitre);
            coursFilterCombo.setDisable(true);
        }
    }

    // ==================== MÉTHODES CRUD ====================

    private void setupTableColumns() {
        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colTitre.setCellValueFactory(new PropertyValueFactory<>("titre"));
        colType.setCellValueFactory(new PropertyValueFactory<>("type"));

        colUrl.setCellValueFactory(new PropertyValueFactory<>("url"));
        colUrl.setCellFactory(col -> new TableCell<RessourcePedagogique, String>() {
            @Override
            protected void updateItem(String item, boolean empty) {
                super.updateItem(item, empty);
                if (empty || item == null || item.isEmpty()) {
                    setText(null);
                    setGraphic(null);
                } else {
                    Hyperlink link = new Hyperlink(item);
                    link.setOnAction(e -> {
                        try {
                            Desktop.getDesktop().browse(new URI(item));
                        } catch (IOException | URISyntaxException ex) {
                            showAlert("Erreur", "Impossible d'ouvrir le lien: " + ex.getMessage(), Alert.AlertType.ERROR);
                        }
                    });
                    setGraphic(link);
                    setText(null);
                }
            }
        });

        colFileName.setCellValueFactory(new PropertyValueFactory<>("file_name"));
        colFileName.setCellFactory(col -> new TableCell<RessourcePedagogique, String>() {
            @Override
            protected void updateItem(String item, boolean empty) {
                super.updateItem(item, empty);
                if (empty || item == null || item.isEmpty()) {
                    setText(null);
                    setGraphic(null);
                } else {
                    try {
                        File file = new File(item);
                        String fileName = file.getName();
                        String displayName = fileName;
                        if (fileName.contains("_")) {
                            int firstUnderscore = fileName.indexOf("_");
                            displayName = fileName.substring(firstUnderscore + 1);
                        }
                        String extension = getFileExtension(fileName);

                        HBox content = new HBox(5);
                        content.setAlignment(Pos.CENTER_LEFT);

                        Label iconLabel = new Label(getFileIcon(extension));
                        iconLabel.setStyle("-fx-font-size: 16px;");

                        if (file.exists()) {
                            Button fileButton = new Button(displayName);
                            fileButton.setStyle("-fx-background-color: transparent; -fx-text-fill: #2196F3; -fx-underline: true; -fx-cursor: hand;");
                            fileButton.setOnAction(e -> {
                                try {
                                    openFile(file);
                                } catch (Exception ex) {
                                    showAlert("Erreur", "Impossible d'ouvrir le fichier: " + ex.getMessage(), Alert.AlertType.ERROR);
                                }
                            });
                            content.getChildren().addAll(iconLabel, fileButton);
                            setGraphic(content);
                            setText(null);
                        } else {
                            Label label = new Label(iconLabel.getText() + " " + displayName + " (fichier non trouvé)");
                            label.setStyle("-fx-text-fill: #f44336;");
                            setGraphic(label);
                            setText(null);
                        }
                    } catch (Exception ex) {
                        setText(item);
                        setGraphic(null);
                    }
                }
            }

            private String getFileExtension(String fileName) {
                int lastDot = fileName.lastIndexOf(".");
                if (lastDot > 0) {
                    return fileName.substring(lastDot + 1).toLowerCase();
                }
                return "";
            }

            private String getFileIcon(String extension) {
                switch (extension) {
                    case "png": case "jpg": case "jpeg": case "gif": case "bmp": case "svg": case "webp":
                        return "🖼️";
                    case "mp4": case "avi": case "mov": case "mkv": case "wmv": case "flv": case "webm":
                        return "🎬";
                    case "mp3": case "wav": case "flac": case "ogg": case "m4a": case "aac":
                        return "🎵";
                    case "pdf":
                        return "📄";
                    case "doc": case "docx": case "xls": case "xlsx": case "ppt": case "pptx": case "txt": case "rtf":
                        return "📝";
                    default:
                        return "📁";
                }
            }
        });

        colDateAjout.setCellValueFactory(new PropertyValueFactory<>("date_ajout"));

        colCours.setCellValueFactory(cellData -> {
            int coursId = cellData.getValue().getCours_id();
            String coursTitre = coursList.stream()
                    .filter(c -> c.getId() == coursId)
                    .map(Cours::getTitre)
                    .findFirst()
                    .orElse("Cours inconnu");
            return new javafx.beans.property.SimpleStringProperty(coursTitre);
        });
    }

    private void openFile(File file) {
        try {
            Desktop.getDesktop().open(file);
        } catch (IOException e) {
            showAlert("Erreur", "Impossible d'ouvrir le fichier: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void chargerCours() {
        try {
            coursList = serviceCours.recuperer();
            coursFilterCombo.getItems().clear();
            coursFilterCombo.getItems().add("Tous les cours");
            for (Cours c : coursList) {
                coursFilterCombo.getItems().add(c.getTitre());
            }
            coursFilterCombo.setValue("Tous les cours");
        } catch (Exception e) {
            showAlert("Erreur", "Impossible de charger les cours: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void chargerRessources() {
        try {
            List<RessourcePedagogique> ressources = serviceRessource.recuperer();
            ressourcesList = FXCollections.observableArrayList(ressources);

            if (coursIdFiltre != -1) {
                filteredList = new FilteredList<>(ressourcesList,
                        ressource -> ressource.getCours_id() == coursIdFiltre);
            } else {
                filteredList = new FilteredList<>(ressourcesList, p -> true);
            }

            SortedList<RessourcePedagogique> sortedList = new SortedList<>(filteredList);
            sortedList.comparatorProperty().bind(tableView.comparatorProperty());
            tableView.setItems(sortedList);
            updateStats();

            if (coursIdFiltre != -1 && coursNomFiltre != null) {
                coursFilterCombo.setValue(coursNomFiltre);
                coursFilterCombo.setDisable(true);
            } else {
                coursFilterCombo.setDisable(false);
            }

        } catch (Exception e) {
            showAlert("Erreur", "Impossible de charger les ressources: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void setupSearchFilter() {
        searchField.textProperty().addListener((observable, oldValue, newValue) -> {
            filteredList.setPredicate(ressource -> {
                if (newValue == null || newValue.isEmpty()) {
                    return true;
                }
                String lowerCaseFilter = newValue.toLowerCase();
                return ressource.getTitre().toLowerCase().contains(lowerCaseFilter) ||
                        ressource.getType().toLowerCase().contains(lowerCaseFilter);
            });
            updateStats();
        });
    }

    private void setupCoursFilter() {
        coursFilterCombo.valueProperty().addListener((observable, oldValue, newValue) -> {
            if (newValue == null || newValue.equals("Tous les cours")) {
                filteredList.setPredicate(ressource -> true);
            } else {
                int coursId = coursList.stream()
                        .filter(c -> c.getTitre().equals(newValue))
                        .map(Cours::getId)
                        .findFirst()
                        .orElse(-1);

                final int finalCoursId = coursId;
                filteredList.setPredicate(ressource -> ressource.getCours_id() == finalCoursId);
            }
            updateStats();
        });
    }

    private void setupActionButtons() {
        colActions.setCellFactory(col -> new TableCell<RessourcePedagogique, Void>() {
            private final Button editBtn = new Button("✏️ Modifier");
            private final Button deleteBtn = new Button("🗑️ Supprimer");
            private final HBox buttons = new HBox(10, editBtn, deleteBtn);

            {
                editBtn.setStyle("-fx-background-color: #2196F3; -fx-text-fill: white; -fx-font-size: 11px; -fx-padding: 5 10; -fx-background-radius: 5; -fx-cursor: hand;");
                deleteBtn.setStyle("-fx-background-color: #f44336; -fx-text-fill: white; -fx-font-size: 11px; -fx-padding: 5 10; -fx-background-radius: 5; -fx-cursor: hand;");
                buttons.setAlignment(Pos.CENTER);
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                if (empty) {
                    setGraphic(null);
                } else {
                    RessourcePedagogique ressource = getTableView().getItems().get(getIndex());
                    editBtn.setOnAction(e -> handleModifier(ressource));
                    deleteBtn.setOnAction(e -> handleSupprimer(ressource));
                    setGraphic(buttons);
                }
            }
        });
    }

    @FXML
    private void handleAjouter() {
        ouvrirDialog(null);
    }

    private void handleModifier(RessourcePedagogique ressource) {
        ouvrirDialog(ressource);
    }

    private void handleSupprimer(RessourcePedagogique ressource) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation");
        alert.setHeaderText("Supprimer la ressource");
        alert.setContentText("Supprimer : " + ressource.getTitre() + " ?");

        if (alert.showAndWait().orElse(ButtonType.CANCEL) == ButtonType.OK) {
            try {
                if (ressource.getFile_name() != null && !ressource.getFile_name().isEmpty()) {
                    File file = new File(ressource.getFile_name());
                    if (file.exists()) {
                        file.delete();
                    }
                }
                serviceRessource.supprimer(ressource);
                chargerRessources();
                showAlert("Succès", "Ressource supprimée avec succès !", Alert.AlertType.INFORMATION);
            } catch (Exception e) {
                showAlert("Erreur", "Erreur lors de la suppression: " + e.getMessage(), Alert.AlertType.ERROR);
            }
        }
    }

    private void ouvrirDialog(RessourcePedagogique ressource) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/RessourceDialog.fxml"));
            VBox dialogVBox = loader.load();

            RessourceDialogController dialogController = loader.getController();
            dialogController.setServiceRessource(serviceRessource);
            dialogController.setCoursList(coursList);
            dialogController.setRessource(ressource);
            dialogController.setParentController(this);

            Stage dialogStage = new Stage();
            dialogStage.initModality(Modality.APPLICATION_MODAL);
            dialogStage.setTitle(ressource == null ? "Ajouter une ressource" : "Modifier une ressource");
            dialogStage.setScene(new Scene(dialogVBox));
            dialogStage.showAndWait();

        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible d'ouvrir le dialogue: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    @FXML
    private void handleActualiser() {
        chargerRessources();
        searchField.clear();
        if (coursIdFiltre == -1) {
            coursFilterCombo.setValue("Tous les cours");
        }
        showAlert("Info", "Liste des ressources actualisée !", Alert.AlertType.INFORMATION);
    }

    @FXML
    private void handleRechercher() {
        updateStats();
    }

    @FXML
    private void handleResetRecherche() {
        searchField.clear();
        chargerRessources();
    }

    private void updateStats() {
        int total = filteredList.size();
        statsLabel.setText(total + " ressource" + (total > 1 ? "s" : "") + " trouvée" + (total > 1 ? "s" : ""));
    }

    public void refreshTable() {
        chargerRessources();
    }

    private void showAlert(String title, String message, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}