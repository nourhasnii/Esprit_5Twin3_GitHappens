This module uses a synthetic cold-chain dataset generated with NumPy.
Train the model and create the dataset with: `python ml/train.py`
The trained model is saved as `ml/model.joblib`.
Predict risk for a temperature and duration with: `python ml/predict.py 12 45`
Prediction output is one line of JSON containing the risk probability.
