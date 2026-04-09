package org.example.Models;

import java.sql.Date;

public class Cours {
    private int id;
    private String titre;
    private String description;
    private String niveau;
    private String matiere;
    private int duree;
    private Date date_creation;
    private String email_prof;
    private String statut;
    private int user_id;

    public Cours() {}

    public Cours(String titre, String description, String niveau, String matiere,
                 int duree, Date date_creation, String email_prof, String statut, int user_id) {
        this.titre = titre;
        this.description = description;
        this.niveau = niveau;
        this.matiere = matiere;
        this.duree = duree;
        this.date_creation = date_creation;
        this.email_prof = email_prof;
        this.statut = statut;
        this.user_id = user_id;
    }

    // Getters & Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getTitre() { return titre; }
    public void setTitre(String titre) { this.titre = titre; }

    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }

    public String getNiveau() { return niveau; }
    public void setNiveau(String niveau) { this.niveau = niveau; }

    public String getMatiere() { return matiere; }
    public void setMatiere(String matiere) { this.matiere = matiere; }

    public int getDuree() { return duree; }
    public void setDuree(int duree) { this.duree = duree; }

    public Date getDate_creation() { return date_creation; }
    public void setDate_creation(Date date_creation) { this.date_creation = date_creation; }

    public String getEmail_prof() { return email_prof; }
    public void setEmail_prof(String email_prof) { this.email_prof = email_prof; }

    public String getStatut() { return statut; }
    public void setStatut(String statut) { this.statut = statut; }

    public int getUser_id() { return user_id; }
    public void setUser_id(int user_id) { this.user_id = user_id; }

    @Override
    public String toString() {
        return "Cours{id=" + id + ", titre='" + titre + "', niveau='" + niveau +
                "', matiere='" + matiere + "', duree=" + duree +
                ", date_creation=" + date_creation + ", email_prof='" + email_prof +
                "', statut='" + statut + "', user_id=" + user_id + "}";
    }
}