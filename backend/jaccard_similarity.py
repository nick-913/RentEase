# import mysql.connector
# from sklearn.feature_extraction.text import CountVectorizer
# from sklearn.metrics import jaccard_score
# import numpy as np

# # -----------------------------
# # 1. Database Connection
# # -----------------------------
# def get_db_connection():
#     return mysql.connector.connect(
#         host="localhost",
#         user="root",          # your DB username
#         password="",          # your DB password
#         database="rent_ease"  # your RentEase database name
#     )

# # -----------------------------
# # 2. Fetch property data
# # -----------------------------
# def fetch_properties():
#     conn = get_db_connection()
#     cursor = conn.cursor(dictionary=True)

#     query = "SELECT property_id, title, location, description FROM properties WHERE status != 'rejected'"
#     cursor.execute(query)
#     properties = cursor.fetchall()

#     cursor.close()
#     conn.close()
#     return properties

# # -----------------------------
# # 3. Preprocess text
# # -----------------------------
# def preprocess_text(prop):
#     return f"{prop['title']} {prop['location']} {prop['description']}"

# # -----------------------------
# # 4. Compute Jaccard Similarity
# # -----------------------------
# def compute_jaccard_similarities(properties):
#     documents = [preprocess_text(p) for p in properties]

#     # Convert text into binary word occurrence matrix
#     vectorizer = CountVectorizer(binary=True, stop_words="english")
#     X = vectorizer.fit_transform(documents)

#     similarities = np.zeros((len(properties), len(properties)))

#     for i in range(len(properties)):
#         for j in range(len(properties)):
#             if i != j:
#                 similarities[i, j] = jaccard_score(X[i].toarray()[0], X[j].toarray()[0])
#     return similarities

# # -----------------------------
# # 5. Recommend similar properties
# # -----------------------------
# def recommend_properties(property_id, top_n=5):
#     properties = fetch_properties()
#     similarities = compute_jaccard_similarities(properties)

#     # find the index of the given property
#     index = next((i for i, p in enumerate(properties) if p["property_id"] == property_id), None)
#     if index is None:
#         print("Property not found")
#         return []

#     # Sort properties by similarity score
#     sim_scores = list(enumerate(similarities[index]))
#     sim_scores = sorted(sim_scores, key=lambda x: x[1], reverse=True)

#     recommendations = []
#     for i, score in sim_scores[:top_n]:
#         if i != index:
#             recommendations.append({
#                 "property_id": properties[i]["property_id"],
#                 "title": properties[i]["title"],
#                 "location": properties[i]["location"],
#                 "score": round(score, 2)
#             })

#     return recommendations

# # -----------------------------
# # 6. Test
# # -----------------------------
# if __name__ == "__main__":
#     test_property_id = 1  # Replace with an existing property_id in your DB
#     recs = recommend_properties(test_property_id, top_n=5)

#     print(f"Recommendations for property {test_property_id}:")
#     for r in recs:
#         print(f" - {r['title']} ({r['location']}) -> Similarity: {r['score']}")
