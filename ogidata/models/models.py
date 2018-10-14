
##### ogidata/models/models.py
from datetime import datetime
from ogidata.database import db

class User(db.Model):

  __tablename__ = 'users'

  id = db.Column(db.Integer, primary_key=True)
  name = db.Column(db.String(255), nullable=False)
  created_at = db.Column(db.DateTime, nullable=False, default=datetime.now)
  updated_at = db.Column(db.DateTime, nullable=False, default=datetime.now, onupdate=datetime.now)

  def to_dict(self):
    return dict(
      id=self.id,
      name=self.name
    )

'''
class ImageInfo(db.Model):
  __tablename__ = 'img_info'

  posted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  img_id INT NOT NULL PRIMARY KEY,
  mime_type VARCHAR(255) NOT NULL,
  img_filename VARCHAR(255) UNIQUE NOT NULL,
  thumbnail_filename VARCHAR(255) UNIQUE NOT NULL,
  img_width INT,
  img_height INT,
  is_removed BOOLEAN NOT NULL DEFAULT FALSE

class TableTitle(db.Model):
  __tablename__ = 'table_title'
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  table_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
  title VARCHAR(255) UNIQUE NOT NULL
'''
