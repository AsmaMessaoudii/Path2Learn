package org.example.controllers;

import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.stage.Stage;
import org.example.Models.User;
import org.example.Services.ServiceUser;

import java.sql.SQLException;

public class EditUserController {

    @FXML private TextField nomField;
    @FXML private TextField prenomField;
    @FXML private TextField emailField;
    @FXML private PasswordField passwordField;

    private User user;
    private ServiceUser serviceUser = new ServiceUser();

    public void setUser(User user) {
        this.user = user;

        nomField.setText(user.getNom());
        prenomField.setText(user.getPrenom());
        emailField.setText(user.getEmail());
        passwordField.setText(user.getMot_de_passe());
    }

    @FXML
    private void handleSave() {
        try {
            user.setNom(nomField.getText());
            user.setPrenom(prenomField.getText());
            user.setEmail(emailField.getText());
            user.setMot_de_passe(passwordField.getText());

            serviceUser.modifier(user); // 👈 UPDATE DB

            Alert alert = new Alert(Alert.AlertType.INFORMATION);
            alert.setContentText("Profil mis à jour !");
            alert.show();

            ((Stage) nomField.getScene().getWindow()).close();

        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}