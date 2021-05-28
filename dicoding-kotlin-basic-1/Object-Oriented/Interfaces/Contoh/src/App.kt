interface ILeo {
    fun leo()
    val numberOfWings: Int
}

class Bird(override val numberOfWings: Int) : ILeo {
    override fun leo() {
        if(numberOfWings > 0) println("Flying with $numberOfWings wings")
        else println("I'm Flying without wings")
    }
}


fun main() {
    val bird = Bird(2)
    bird.leo()
}