package org.example.Models;

import java.sql.Date;
import java.sql.Timestamp;

public class RessourcePedagogique {
    private int id;
    private String titre;
    private String type;
    private String url;
    private Date date_ajout;
    private int cours_id;
    private String file_name;
    private Timestamp updated_at;

    public RessourcePedagogique() {}

    public RessourcePedagogique(String titre, String type, String url, Date date_ajout,
                                int cours_id, String file_name, Timestamp updated_at) {
        this.titre = titre;
        this.type = type;
        this.url = url;
        this.date_ajout = date_ajout;
        this.cours_id = cours_id;
        this.file_name = file_name;
        this.updated_at = updated_at;
    }

    // Getters & Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getTitre() { return titre; }
    public void setTitre(String titre) { this.titre = titre; }

    public String getType() { return type; }
    public void setType(String type) { this.type = type; }

    public String getUrl() { return url; }
    public void setUrl(String url) { this.url = url; }

    public Date getDate_ajout() { return date_ajout; }
    public void setDate_ajout(Date date_ajout) { this.date_ajout = date_ajout; }

    public int getCours_id() { return cours_id; }
    public void setCours_id(int cours_id) { this.cours_id = cours_id; }

    public String getFile_name() { return file_name; }
    public void setFile_name(String file_name) { this.file_name = file_name; }

    public Timestamp getUpdated_at() { return updated_at; }
    public void setUpdated_at(Timestamp updated_at) { this.updated_at = updated_at; }

    @Override
    public String toString() {
        return "RessourcePedagogique{id=" + id + ", titre='" + titre + "', type='" + type +
                "', url='" + url + "', date_ajout=" + date_ajout +
                ", cours_id=" + cours_id + ", file_name='" + file_name +
                "', updated_at=" + updated_at + "}";
    }
}