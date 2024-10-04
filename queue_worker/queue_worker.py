import redis
import pymysql
import json
import time

# Redis connection
redis_client = redis.StrictRedis(host='localhost', port=6379, db=0)

# MySQL database connection
db = pymysql.connect(
    host="localhost",
    user="root",           # Change this to your MySQL user
    password="12345678",    # Change this to your MySQL password
    database="sports_db",  # Change this to your MySQL database name
    cursorclass=pymysql.cursors.DictCursor
)

# Function to process queue messages
def process_task(task):
    try:
        task = json.loads(task)
        action = task['action']
        data = task['data']

        if action == 'create_player':
            create_player(data)
        elif action == 'update_player':
            update_player(data)
        elif action == 'delete_player':
            delete_player(data)
        elif action == 'create_game':
            create_game(data)
        elif action == 'update_game':
            update_game(data)
        elif action == 'delete_game':
            delete_game(data)
        elif action == 'create_statistic':
            create_statistic(data)
        elif action == 'update_statistic':
            update_statistic(data)
        elif action == 'delete_statistic':
            delete_statistic(data)

    except Exception as e:
        print(f"Error processing task: {e}")

# CRUD operations for players
def create_player(data):
    with db.cursor() as cursor:
        sql = """
        INSERT INTO players (first_name, last_name, position, height, weight, age, experience, college, image)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        cursor.execute(sql, (data['first_name'], data['last_name'], data['position'], data['height'], 
                             data['weight'], data['age'], data['experience'], data['college'], data.get('image')))
        db.commit()
        print("Player created successfully.")

def update_player(data):
    with db.cursor() as cursor:
        sql = """
        UPDATE players 
        SET first_name=%s, last_name=%s, position=%s, height=%s, weight=%s, age=%s, experience=%s, college=%s, image=%s
        WHERE player_id=%s
        """
        cursor.execute(sql, (data['first_name'], data['last_name'], data['position'], data['height'], 
                             data['weight'], data['age'], data['experience'], data['college'], data.get('image'), data['player_id']))
        db.commit()
        print("Player updated successfully.")

def delete_player(data):
    with db.cursor() as cursor:
        sql = "DELETE FROM players WHERE player_id=%s"
        cursor.execute(sql, (data['player_id'],))
        db.commit()
        print("Player deleted successfully.")

# CRUD operations for games
def create_game(data):
    with db.cursor() as cursor:
        sql = """
        INSERT INTO games (game_date, opponent, location, team_score, opponent_score, result, season, opponent_image)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
        """
        cursor.execute(sql, (data['game_date'], data['opponent'], data['location'], data['team_score'], 
                             data['opponent_score'], data['result'], data['season'], data.get('opponent_image')))
        db.commit()
        print("Game created successfully.")

def update_game(data):
    with db.cursor() as cursor:
        sql = """
        UPDATE games 
        SET game_date=%s, opponent=%s, location=%s, team_score=%s, opponent_score=%s, result=%s, season=%s, opponent_image=%s
        WHERE game_id=%s
        """
        cursor.execute(sql, (data['game_date'], data['opponent'], data['location'], data['team_score'], 
                             data['opponent_score'], data['result'], data['season'], data.get('opponent_image'), data['game_id']))
        db.commit()
        print("Game updated successfully.")

def delete_game(data):
    with db.cursor() as cursor:
        sql = "DELETE FROM games WHERE game_id=%s"
        cursor.execute(sql, (data['game_id'],))
        db.commit()
        print("Game deleted successfully.")

# CRUD operations for player_statistics
def create_statistic(data):
    with db.cursor() as cursor:
        sql = """
        INSERT INTO player_statistics (player_id, game_id, season, season_rank, game_rank)
        VALUES (%s, %s, %s, %s, %s)
        """
        cursor.execute(sql, (data['player_id'], data['game_id'], data['season'], data['season_rank'], data['game_rank']))
        db.commit()
        print("Player statistic created successfully.")

def update_statistic(data):
    with db.cursor() as cursor:
        sql = """
        UPDATE player_statistics 
        SET player_id=%s, game_id=%s, season=%s, season_rank=%s, game_rank=%s
        WHERE stats_id=%s
        """
        cursor.execute(sql, (data['player_id'], data['game_id'], data['season'], data['season_rank'], 
                             data['game_rank'], data['stats_id']))
        db.commit()
        print("Player statistic updated successfully.")

def delete_statistic(data):
    with db.cursor() as cursor:
        sql = "DELETE FROM player_statistics WHERE stats_id=%s"
        cursor.execute(sql, (data['stats_id'],))
        db.commit()
        print("Player statistic deleted successfully.")

# Queue worker loop
while True:
    # Fetch task from Redis (blocking)
    task = redis_client.blpop('task_queue', timeout=0)  # 'task_queue' is the Redis queue name
    if task:
        process_task(task[1])
    else:
        time.sleep(1)
