fun main() {
    val text = "Hello Kotlin"
    val leo = text.also {
        println("value length -> ${it.length}")
    }

    println("text -> $leo")
}