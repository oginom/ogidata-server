
##### ogidata/app.py
"""flask appの初期化を行い、flask appオブジェクトの実体を持つ"""
from flask import Flask, jsonify, request

from ogidata.database import init_db
import ogidata.models


def create_app():
  app = Flask(__name__)
  app.config.from_object('ogidata.config.Config')

  init_db(app)

  return app

app = create_app()

'''
@app.route("/api/v1/model/<id>", methods=['DELETE'])
def api_v1_model_id(id):
  if request.method == 'DELETE':
    d = ogidata.models.User.query.get(id)
    db.session.delete(d)
    db.session.commit()
    return '', 204
'''

@app.route("/api/v1/models", methods=['GET', 'POST'])
def api_v1_models():
  '''
  if request.method == 'POST':
    name = request.json['name']
    d = ogidata.models.User(name)
    db.session.add(d)
    db.session.commit()
    return jsonify(d.to_dict()), 201
  '''
  if request.method == 'GET':
    ls = ogidata.models.User.query.all()
    ls = [l.to_dict() for l in ls]
    return jsonify(ls), 200
