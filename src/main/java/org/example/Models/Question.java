package org.example.Models;

import java.util.Date;

public class Question {
    private int id;
    private String titre;
    private String description;
    private Date dateCreation;
    private int duree;
    private float noteMax;
    private int userId;

    // Constructeur vide
    public Question() {}

    // Constructeur SANS id (ajout)
    public Question(String titre, String description, Date dateCreation, int duree, float noteMax, int userId) {
        this.titre = titre;
        this.description = description;
        this.dateCreation = dateCreation;
        this.duree = duree;
        this.noteMax = noteMax;
        this.userId = userId;
    }

    // Constructeur AVEC id (modifier/supprimer)
    public Question(int id, String titre, String description, Date dateCreation, int duree, float noteMax, int userId) {
        this.id = id;
        this.titre = titre;
        this.description = description;
        this.dateCreation = dateCreation;
        this.duree = duree;
        this.noteMax = noteMax;
        this.userId = userId;
    }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getTitre() { return titre; }
    public void setTitre(String titre) { this.titre = titre; }

    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }

    public Date getDateCreation() { return dateCreation; }
    public void setDateCreation(Date dateCreation) { this.dateCreation = dateCreation; }

    public int getDuree() { return duree; }
    public void setDuree(int duree) { this.duree = duree; }

    public float getNoteMax() { return noteMax; }
    public void setNoteMax(float noteMax) { this.noteMax = noteMax; }

    public int getUserId() { return userId; }
    public void setUserId(int userId) { this.userId = userId; }

    @Override
    public String toString() {
        return "Question{" +
                "id=" + id +
                ", titre='" + titre + '\'' +
                ", description='" + description + '\'' +
                ", dateCreation=" + dateCreation +
                ", duree=" + duree +
                ", noteMax=" + noteMax +
                ", userId=" + userId +
                "}\n";
    }
// Add these methods to your Question class

    public float getPoints() {
        return noteMax;
    }

    public void setPoints(float points) {
        this.noteMax = points;
    }

}
