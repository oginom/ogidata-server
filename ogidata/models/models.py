
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


class ImageInfo(db.Model):
  __tablename__ = 'img_info'

  posted_at = db.Column(db.DateTime, nullable=False, default=datetime.now)
  img_id = db.Column(db.Integer, nullable=False, primary_key=True)
  img_ext = db.Column(db.String(length=255), nullable=False)
  img_filename = db.Column(db.String(length=255), unique=True, nullable=False)
  thumbnail_filename = db.Column(db.String(length=255), unique=True, nullable=False)
  img_width = db.Column(db.Integer)
  img_height = db.Column(db.Integer)
  is_removed = db.Column(db.Boolean, nullable=False, default=False)
  '''
  posted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  img_id INT NOT NULL PRIMARY KEY,
  mime_type VARCHAR(255) NOT NULL,
  img_filename VARCHAR(255) UNIQUE NOT NULL,
  thumbnail_filename VARCHAR(255) UNIQUE NOT NULL,
  img_width INT,
  img_height INT,
  is_removed BOOLEAN NOT NULL DEFAULT FALSE
  '''

  def __init__(self, img_id, img_filename,
      thumbnail_filename, img_ext,
      img_width, img_height):
    self.img_id = img_id
    self.img_filename = img_filename
    self.thumbnail_filename = thumbnail_filename
    self.img_ext = img_ext
    self.img_width = img_width
    self.img_height = img_height

  def as_dict(self):
    return {
      'img_id':self.img_id,
      'img_ext':self.img_ext,
      'img_filename':self.img_filename,
      'thumbnail_filename':self.thumbnail_filename,
      'img_width':self.img_width,
      'img_height':self.img_height,
    }

class TableTitle(db.Model):
  __tablename__ = 'table_title'

  created_at = db.Column(db.DateTime, nullable=False, default=datetime.now)
  table_id = db.Column(db.Integer, autoincrement=True, nullable=False,
    primary_key=True)
  title = db.Column(db.String(length=255), unique=True, nullable=False)
  '''
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  table_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
  title VARCHAR(255) UNIQUE NOT NULL
  '''

  def __init__(self, title):
    self.title = title

  def to_dict(self):
    return dict(
      table_id=self.table_id,
      title=self.title
    )

