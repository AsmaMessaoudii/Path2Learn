package org.example.Models;

import java.sql.Date;

public class User {
    private int id;
    private String nom;
    private String prenom;
    private String email;
    private String mot_de_passe;
    private String role;
    private String status;
    private java.sql.Timestamp date_creation;

    // Constructeur vide
    public User() {}

    // Constructeur sans id (pour INSERT)
    public User(String nom, String prenom, String email, String mot_de_passe,
                String role, String status, java.sql.Timestamp date_creation) {
        this.nom = nom;
        this.prenom = prenom;
        this.email = email;
        this.mot_de_passe = mot_de_passe;
        this.role = role;
        this.status = status;
        this.date_creation = date_creation;
    }

    // Constructeur avec id (pour UPDATE/DELETE)
    public User(int id, String nom, String prenom, String email, String mot_de_passe,
                String role, String status, java.sql.Timestamp date_creation) {
        this.id = id;
        this.nom = nom;
        this.prenom = prenom;
        this.email = email;
        this.mot_de_passe = mot_de_passe;
        this.role = role;
        this.status = status;
        this.date_creation = date_creation;
    }

    // Getters & Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getNom() { return nom; }
    public void setNom(String nom) { this.nom = nom; }

    public String getPrenom() { return prenom; }
    public void setPrenom(String prenom) { this.prenom = prenom; }

    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }

    public String getMot_de_passe() { return mot_de_passe; }
    public void setMot_de_passe(String mot_de_passe) { this.mot_de_passe = mot_de_passe; }

    public String getRole() { return role; }
    public void setRole(String role) { this.role = role; }

    public String getStatus() { return status; }
    public void setStatus(String status) { this.status = status; }

    public java.sql.Timestamp getDate_creation() { return date_creation; }
    public void setDate_creation(java.sql.Timestamp date_creation) { this.date_creation = date_creation; }



    @Override
    public String toString() {
        return "User{id=" + id +
                ", nom='" + nom + "'" +
                ", prenom='" + prenom + "'" +
                ", email='" + email + "'" +
                ", role='" + role + "'" +
                ", status='" + status + "'" +
                ", date_creation=" + date_creation + "}";
    }
}