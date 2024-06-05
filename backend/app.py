from flask import Flask, request, jsonify, send_file
from flask_cors import CORS
from sklearn.tree import export_graphviz
from sklearn.tree import DecisionTreeClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import confusion_matrix, classification_report, accuracy_score, precision_score, recall_score, f1_score
import pandas as pd
import joblib  # Import ini jika Anda menyimpan model menggunakan joblib
import graphviz
import logging
from sklearn.metrics import classification_report, confusion_matrix
from graphviz import Source

app = Flask(__name__)
CORS(app)

logging.basicConfig(level=logging.DEBUG, format='%(asctime)s - %(levelname)s - %(message)s')

# Variabel global untuk menyimpan data dan model
model = None
X = None
y = None


# Memuat data
df = pd.read_excel('data100.xlsx')


# Preprocessing
df = pd.get_dummies(df, columns=['service'], prefix=['service'])

# Menghapus kolom yang tidak diperlukan
df.drop(['no.'], axis=1, inplace=True)

# Memisahkan fitur dan label
X = df.drop('attack_cat', axis=1)
y = df['attack_cat']

# Membagi data menjadi train dan test set
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Melatih model
model = DecisionTreeClassifier(criterion='entropy', random_state=42)
model.fit(X_train, y_train)

# Menyimpan model dan fitur untuk digunakan di fungsi prediksi
feature_names = X_train.columns.tolist()

@app.route('/load_data', methods=['GET'])
def load_data():
    global X, y
    # Pastikan untuk mengupdate path file sesuai lokasi file Anda
    df = pd.read_excel('data100.xlsx')

    
    # Mengubah data kategorikal menjadi numerik
    # df_encoded = pd.get_dummies(df[['service']], prefix=['service'])
    # Menggabungkan data numerik dengan data asli
    # df = pd.concat([df, df_encoded], axis=1)
    
    X = df.drop(['no.', 'attack_cat', 'service'], axis=1)
    y = df['attack_cat']
    data_json = df.to_json(orient='records')
    return jsonify({'data': data_json})
    # return jsonify({'message': 'Data loaded successfully'}), 200

@app.route('/add-data', methods=['POST'])
def add_data():
    try:
        data = request.get_json()
        df = pd.read_excel('data100.xlsx')
        new_row = pd.DataFrame(data, index=[0])
        df = pd.concat([df, new_row], ignore_index=True)
        df = pd.read_excel('data100.xlsx')
        return jsonify({'message': 'Data added successfully'}), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500


# @app.route('/train', methods=['POST'])
# def train_model():
#     global X, y, model
#     if X is None or y is None:
#         return jsonify({'error': 'Data not loaded'}), 400

#     # Memilih atribut terbaik secara otomatis
#     best_gain_ratio = -1
#     best_attribute = None
#     for column in X.columns:
#         temp_model = DecisionTreeClassifier(criterion='entropy')
#         temp_model.fit(X[[column]], y)
#         gain_ratio = temp_model.tree_.impurity[0] - temp_model.tree_.impurity[1]
#         if gain_ratio > best_gain_ratio:
#             best_gain_ratio = gain_ratio
#             best_attribute = column

#     model = DecisionTreeClassifier(criterion='entropy', random_state=42)
#     model.fit(X[[best_attribute]], y)
#     return jsonify({'message': 'Model trained successfully', 'best_attribute': best_attribute}), 200

# @app.route('/train', methods=['POST'])
# def train_model():
#     print(request.json)
#     global X, y, model
#     if X is None or y is None:
#         return jsonify({'error': 'Data not loaded'}), 400

#     # Menerima parameter dari request
#     params = request.get_json() or {}
#     min_samples_split = params.get('min_samples_split', 2)  # Default value is 2
#     random_state = params.get('random_state', 42)  # Default value is 42

#     # Memilih atribut terbaik secara otomatis
#     best_gain_ratio = -1
#     best_attribute = None
#     for column in X.columns:
#         temp_model = DecisionTreeClassifier(criterion='entropy')
#         temp_model.fit(X[[column]], y)
#         gain_ratio = temp_model.tree_.impurity[0] - temp_model.tree_.impurity[1]
#         if gain_ratio > best_gain_ratio:
#             best_gain_ratio = gain_ratio
#             best_attribute = column

#     model = DecisionTreeClassifier(criterion='entropy', random_state=random_state, min_samples_split=min_samples_split)
#     model.fit(X[[best_attribute]], y)
#     return jsonify({'message': 'Model trained successfully', 'best_attribute': best_attribute}), 200

# @app.route('/train', methods=['POST'])
# def train_model():
#     try:
#         # Langkah 1: Muat data dari CSV
#         df = pd.read_csv('data20.csv')
#         # df_encoded = pd.get_dummies(df[['service']], prefix=['service'])
#         # df = pd.concat([df, df_encoded], axis=1)
#         # Preprocessing
#         df = pd.get_dummies(df, columns=['service'], prefix=['service'])

#         # Menghapus kolom yang tidak diperlukan
#         df.drop(['no.'], axis=1, inplace=True)

#         # Memisahkan fitur dan label
#         X = df.drop('attack_cat', axis=1)
#         y = df['attack_cat']
        
#         # Langkah 2: Menerima parameter dari request
#         params = request.get_json() or {}
#         min_samples_split = params.get('min_samples_split', 2)  # Nilai default
#         random_state = params.get('random_state', 42)  # Nilai default
        
#         # Langkah 3: Memilih atribut terbaik secara otomatis
#         best_gain_ratio = -1
#         best_attribute = None
#         best_accuracy = 0
        
#         for column in X.columns:
#             temp_model = DecisionTreeClassifier(criterion='entropy', random_state=random_state)
#             temp_model.fit(X[[column]], y)
#             predictions = temp_model.predict(X[[column]])
#             accuracy = accuracy_score(y, predictions)
#             gain_ratio = temp_model.tree_.impurity[0] - temp_model.tree_.impurity[1]
            
#             if accuracy > best_accuracy or (accuracy == best_accuracy and gain_ratio > best_gain_ratio):
#                 best_gain_ratio = gain_ratio
#                 best_attribute = column
#                 best_accuracy = accuracy

#         # Langkah 4: Latih model dengan atribut terbaik
#         global model
#         model = DecisionTreeClassifier(criterion='entropy', random_state=random_state, min_samples_split=min_samples_split)
#         model.fit(X[[best_attribute]], y)
        
#         return jsonify({
#             'message': 'Model trained successfully',
#             'best_attribute': best_attribute,
#             'accuracy': best_accuracy
#         }), 200

#     except Exception as e:
#         return jsonify({'error': str(e)}), 500

@app.route('/train', methods=['POST'])
def train_model():
    try:
        df = pd.read_excel('data100.xlsx')
        
        # Assuming 'service' is categorical and needs encoding
        df = pd.get_dummies(df, columns=['service'])

        X = df.drop(['no.', 'attack_cat'], axis=1)
        # X = df.drop('attack_cat', axis=1)  # Assuming 'attack_cat' is the label
        y = df['attack_cat']

        model = DecisionTreeClassifier()
        model.fit(X, y)
        joblib.dump(model, 'model.pkl')  # Saving the model
        return jsonify({'message': 'Model trained successfully'}), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500
    
@app.route('/model_info', methods=['GET'])
def model_info():
    try:
        # Memuat model yang disimpan
        model = joblib.load('model.pkl')
        
        # Mendapatkan informasi yang diinginkan, misalnya parameter model
        model_params = model.get_params()
        
        # Mengembalikan parameter sebagai JSON
        return jsonify(model_params)
    except Exception as e:
        return jsonify({'error': str(e)}), 500
    
    #Memakai Entrophy
@app.route('/train_full', methods=['POST'])
def train_model2():
    try:
        # Load data
        data_path = request.json.get('data_path', 'data100.xlsx')
        
        try:
            df = pd.read_csv(data_path, encoding='utf-8')
        except UnicodeDecodeError:
            logging.warning("UTF-8 encoding failed, trying 'latin1'")
            df = pd.read_csv(data_path, encoding='latin1')
        except pd.errors.ParserError as e:
            logging.error(f"Parser error: {str(e)}. Attempting to read with error_bad_lines=False.")
            df = pd.read_csv(data_path, encoding='latin1', error_bad_lines=False)

        # Validate necessary columns
        if 'attack_cat' not in df.columns or 'service' not in df.columns:
            return jsonify({'error': 'Dataset must contain \'attack_cat\' and \'service\' columns'}), 400

        # Preprocess data
        df = pd.get_dummies(df, columns=['service'])
        X = df.drop(['no.', 'attack_cat'], axis=1)
        y = df['attack_cat']
        
        # Capture unique class names
        class_names = y.unique().tolist()
        class_names.sort()  # Sorting to ensure consistent order

        # Split data
        test_size = request.json.get('test_size', 0.2)
        random_state = request.json.get('random_state', 42)
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=test_size, random_state=random_state)

        # Train model using entropy as the criterion
        criterion = request.json.get('criterion', 'entropy')  # Changed from 'gini' to 'entropy'
        max_depth = request.json.get('max_depth', None)
        model = DecisionTreeClassifier(criterion=criterion, max_depth=max_depth)
        model.fit(X_train, y_train)

        # Evaluate model
        y_pred = model.predict(X_test)
        accuracy = accuracy_score(y_test, y_pred)
        logging.info(f'Model trained successfully with accuracy: {accuracy}')

        # Save the model
        model_path = request.json.get('model_path', 'model.pkl')
        joblib.dump(model, model_path)

         # Create and save the tree diagram
        dot_data = export_graphviz(model, out_file=None, feature_names=X.columns, class_names=class_names, filled=True, rounded=True, special_characters=True)
        graph = Source(dot_data)
        graph_path = 'tree_graph'
        graph.render(graph_path, format='png', cleanup=True)

        # Return file
        return send_file(graph_path + '.png', mimetype='image/png')

    except Exception as e:
        logging.error(f"An error occurred: {str(e)}")
        return jsonify({'error': str(e)}), 500

    
    
# Memakai GINI
# @app.route('/train_full', methods=['POST'])
# def train_model2():
#     try:
#         # Load data
#         data_path = request.json.get('data_path', 'data20.csv')
#         df = pd.read_csv(data_path)

#         # Validate necessary columns
#         if 'attack_cat' not in df.columns or 'service' not in df.columns:
#             return jsonify({'error': 'Dataset must contain \'attack_cat\' and \'service\' columns'}), 400

#         # Preprocess data
#         df = pd.get_dummies(df, columns=['service'])
#         X = df.drop(['no.', 'attack_cat'], axis=1)
#         # X = df.drop('no.','attack_cat', axis=1)
#         y = df['attack_cat']

#         # Split data
#         test_size = request.json.get('test_size', 0.2)
#         random_state = request.json.get('random_state', 42)
#         X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=test_size, random_state=random_state)

#         # Train model
#         criterion = request.json.get('criterion', 'gini')
#         max_depth = request.json.get('max_depth', None)
#         model = DecisionTreeClassifier(criterion=criterion, max_depth=max_depth)
#         model.fit(X_train, y_train)

#         # Evaluate model
#         y_pred = model.predict(X_test)
#         accuracy = accuracy_score(y_test, y_pred)
#         logging.info(f'Model trained successfully with accuracy: {accuracy}')

#         # Save the model
#         model_path = request.json.get('model_path', 'model.pkl')
#         joblib.dump(model, model_path)

#         # Create and save the tree diagram
#         dot_data = export_graphviz(model, out_file=None, feature_names=X.columns, class_names=True, filled=True, rounded=True, special_characters=True)
#         graph = Source(dot_data)
#         graph_path = 'tree_graph'
#         graph.render(graph_path, format='png', cleanup=True)

#         # Return file
#         return send_file(graph_path + '.png', mimetype='image/png')

#     except Exception as e:
#         response_data = {'image_url': '/static/tree_graph.png'}
#         logging.debug(f"Response being sent: {response_data}")
#         return jsonify(response_data)

    
@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json()
    if not data:
        return jsonify({'error': 'No data provided'}), 400
    
    try:
        # Assume `service` needs one-hot encoding like during training
        new_data = pd.DataFrame([data])
        new_data = pd.get_dummies(new_data)
        
        # Realign columns as per the trained model's expectations
        model = joblib.load('model.pkl')
        train_columns = model.feature_names_in_  # This is available if you're using sklearn 1.0+
        for col in train_columns:
            if col not in new_data.columns:
                new_data[col] = 0
        new_data = new_data[train_columns]
        
        prediction = model.predict(new_data)
        return jsonify({'prediction': prediction.tolist()}), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/tree', methods=['GET'])
def get_tree():
    try:
        model = joblib.load('model.pkl')
        # Check the number of features
        print("Number of features in the model:", model.n_features_)
        if len(X.columns) != model.n_features_:
            raise ValueError("Mismatch in the number of features")

        dot_data = export_graphviz(model, out_file=None, filled=True, rounded=True,
                                   special_characters=True, feature_names=X.columns, 
                                   class_names=['class1', 'class2'])
        return jsonify({'dot_data': dot_data}), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500
@app.route('/testc45', methods=['GET'])
def test_c45():
    logging.debug("Starting the test_c45 function.")
    try:
        # Load data
        logging.info("Loading data from Excel.")  
        df = pd.read_excel('data100.xlsx')

        # Convert categorical data to numeric
        X = df.drop(['no.', 'attack_cat', 'service'], axis=1)
        y = df['attack_cat']

        # Calculate gain ratios
        gain_ratios = []
        logging.info("Calculating gain ratios.")  
        for column in X.columns:
            temp_model = DecisionTreeClassifier(criterion='entropy')
            temp_model.fit(X[[column]], y)
            initial_impurity = temp_model.tree_.impurity[0]
            child_impurity = sum(temp_model.tree_.impurity[1:])
            gain_ratio = initial_impurity - child_impurity
            gain_ratios.append({'feature': column, 'gain_ratio': gain_ratio})

        # Select the best attribute based on the highest gain ratio
        best_attribute = max(gain_ratios, key=lambda x: x['gain_ratio'])['feature']
        logging.info(f"Best attribute determined: {best_attribute}")

        # Train the model with all features
        model = DecisionTreeClassifier(criterion='entropy', random_state=42)
        model.fit(X, y)


        # Get test_size parameter from request
        test_size = request.args.get('test_size', default=0.2, type=float)

        # Train-test split
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=test_size, random_state=42)

        # Train the model
        model = DecisionTreeClassifier(criterion='entropy', random_state=42)
        model.fit(X_train, y_train)

        # Evaluate the model
        # X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.5, random_state=42)
        y_pred = model.predict(X_test)
        
        # Evaluate the model
        accuracy = accuracy_score(y_test, y_pred)
        precision = precision_score(y_test, y_pred, average='weighted', zero_division=0)
        recall = recall_score(y_test, y_pred, average='weighted', zero_division=0)
        f1 = f1_score(y_test, y_pred, average='weighted', zero_division=0)
        # cm = confusion_matrix(y_test, y_pred)
        
        report = classification_report(y_test, y_pred, zero_division=0)
        cm = confusion_matrix(y_test, y_pred)

        # Calculate TP, TN, FP, FN for each class
        TP = cm.diagonal()
        FP = cm.sum(axis=0) - TP
        FN = cm.sum(axis=1) - TP
        TN = cm.sum() - (FP + FN + TP)
        
        class_labels = [str(cls) for cls in model.classes_]
        confusion_details = {
            cls: {
                'TP': int(tp),
                'TN': int(tn),
                'FP': int(fp),
                'FN': int(fn)
            } for cls, tp, tn, fp, fn in zip(class_labels, TP, TN, FP, FN)
        }
        logging.info(f"Confusion details: {confusion_details}")

        # Save the trained model
        joblib.dump(model, 'model.pkl')

        # Create decision tree graph
        dot_data = export_graphviz(model, out_file=None, feature_names=X.columns, class_names=[str(uni) for uni in y.unique()], filled=True, rounded=True, special_characters=True)
        
        # Return all details
        return jsonify({
                'accuracy': accuracy,
                'confusion_matrix': cm.tolist(),
                'tn': int(TN.sum()),
                'fp': int(FP.sum()),
                'fn': int(FN.sum()),
                'tp': int(TP.sum()),
                'classification_report': report,
                'precision': precision,
                'recall': recall,
                'f1_score': f1,
                'confusion_details': confusion_details,
                'decision_tree_dot': dot_data
            }), 200

    except Exception as e:
        return jsonify({'error': str(e)}), 500
  

# @app.route('/testc45', methods=['GET'])
# def test_c45():
#     logging.debug("Starting the test_c45 function.")
#     try:
#         logging.info("Loading data from Excel.")
#         df = pd.read_excel('data20.xlsx')

#         logging.info("Converting categorical data to numeric.")
#         df_encoded = pd.get_dummies(df[['service']], prefix=['service'])
#         df = pd.concat([df, df_encoded], axis=1)
#         X = df.drop(['no.', 'attack_cat', 'service'], axis=1)
#         y = df['attack_cat']

#         gain_ratios = []
#         logging.info("Calculating gain ratios.")
#         for column in X.columns:
#             temp_model = DecisionTreeClassifier(criterion='entropy')
#             temp_model.fit(X[[column]], y)
#             initial_impurity = temp_model.tree_.impurity[0]
#             child_impurity = sum(temp_model.tree_.impurity[1:])
#             gain_ratio = initial_impurity - child_impurity
#             gain_ratios.append({'feature': column, 'gain_ratio': gain_ratio})

#         best_attribute = max(gain_ratios, key=lambda x: x['gain_ratio'])['feature']
#         logging.info(f"Best attribute determined: {best_attribute}")

#         model = DecisionTreeClassifier(criterion='entropy', random_state=42)
#         X_train, X_test, y_train, y_test = train_test_split(X[[best_attribute]], y, test_size=0.2, random_state=42)
#         model.fit(X_train, y_train)
#         y_pred = model.predict(X_test)

#         report = classification_report(y_test, y_pred, zero_division=0)
#         cm = confusion_matrix(y_test, y_pred)
#         joblib.dump(model, 'model.pkl')

#         dot_data = export_graphviz(model, out_file=None, feature_names=[best_attribute], class_names=[str(uni) for uni in y.unique()], filled=True, rounded=True, special_characters=True)

#         return jsonify({
#             'message': 'Model trained successfully',
#             'best_attribute': best_attribute,
#             'gain_ratios': gain_ratios,
#             'classification_report': report,
#             'confusion_matrix': cm.tolist(),
#             'decision_tree_dot': dot_data
#         }), 200

#     except Exception as e:
#         logging.error(f"Error in test_c45 function: {str(e)}", exc_info=True)
#         return jsonify({'error': str(e)}), 500


# @app.route('/predict', methods=['POST'])
# def predict():
#     data = request.get_json()
#     if not data:
#         return jsonify({'error': 'No data provided'}), 400

#     try:
#         # Membuat DataFrame dari data yang diberikan
#         new_data = pd.DataFrame([data])
#         new_data = pd.get_dummies(new_data, columns=['service'], prefix=['service'])

#         # Pastikan semua fitur yang digunakan saat training ada di data baru
#         missing_cols = set(feature_names) - set(new_data.columns)
#         for col in missing_cols:
#             new_data[col] = 0
#         new_data = new_data[feature_names]

#         # Prediksi menggunakan model
#         prediction = model.predict(new_data)
#         return jsonify({'prediction': prediction.tolist()}), 200
#     except Exception as e:
#         return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True)
